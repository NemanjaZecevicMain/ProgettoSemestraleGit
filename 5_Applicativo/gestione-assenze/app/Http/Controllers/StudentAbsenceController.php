<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\MedicalCertificate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\SignatureConfirmation;
use App\Mail\AbsenceSignatureLinkMail;
use App\Mail\AbsenceApprovalRequestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use App\Support\AuditLogger;
use Throwable;

class StudentAbsenceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('student.absences.access')) {
            abort(403);
        }

        $status = $request->input('status', 'all');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Absence::query()
            ->where('student_id', $user->id)
            ->orderByDesc('date_from');

        if ($status !== 'all' && $status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($dateFrom) {
            $query->whereDate('date_from', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('date_to', '<=', $dateTo);
        }

        $absences = $query->paginate(10)->withQueryString();

        $statusOptions = [
            'PENDING' => 'In attesa',
            'WAITING_CERT' => 'Attesa certificato',
            'WAITING_SIGNATURE' => 'Attesa firma',
            'JUSTIFIED' => 'Giustificata',
            'UNJUSTIFIED' => 'Non giustificata',
        ];

        return view('student.absences.index', [
            'absences' => $absences,
            'statusOptions' => $statusOptions,
            'filters' => [
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('student.absences.access')) {
            abort(403);
        }

        $reasonOptions = $this->reasonOptions();
        $slotOptions = $this->slotOptions();

        return view('student.absences.create', [
            'reasonOptions' => $reasonOptions,
            'slotOptions' => $slotOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('student.absences.access')) {
            abort(403);
        }

        $validated = $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'start_slot' => ['required', 'array', 'min:1'],
            'start_slot.*' => ['string'],
            'end_slot' => [
                Rule::requiredIf(fn () => $request->input('date_from') !== $request->input('date_to')),
                'nullable',
                'array',
            ],
            'end_slot.*' => ['string'],
            'reason' => ['required', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'date_from.required' => 'Seleziona la data di inizio.',
            'date_to.required' => 'Seleziona la data di fine.',
            'date_to.after_or_equal' => 'La data di fine deve essere uguale o successiva alla data di inizio.',
            'start_slot.required' => 'Seleziona almeno un orario per il primo giorno.',
            'end_slot.required' => 'Seleziona almeno un orario per l\'ultimo giorno.',
            'reason.required' => 'Seleziona una motivazione.',
        ]);

        $reasonOptions = $this->reasonOptions();
        if (!array_key_exists($validated['reason'], $reasonOptions)) {
            return redirect()
                ->back()
                ->withErrors(['reason' => 'Motivazione non valida.'])
                ->withInput();
        }

        $slotOptions = $this->slotOptions();
        $startSlots = $validated['start_slot'] ?? [];
        foreach ($startSlots as $slot) {
            if (!array_key_exists($slot, $slotOptions)) {
                return redirect()
                    ->back()
                    ->withErrors(['start_slot' => 'Orario primo giorno non valido.'])
                    ->withInput();
            }
        }

        if (!$this->isRequestAtLeast24HoursBeforeStart($validated['date_from'], $startSlots)) {
            return redirect()
                ->back()
                ->withErrors(['date_from' => 'La segnalazione va inviata almeno 24 ore prima dell\'inizio dell\'assenza.'])
                ->withInput();
        }

        $sameDay = $validated['date_from'] === $validated['date_to'];
        $endSlots = $validated['end_slot'] ?? [];
        if (!$sameDay && count($endSlots) === 0) {
            return redirect()
                ->back()
                ->withErrors(['end_slot' => 'Seleziona almeno un orario per l\'ultimo giorno.'])
                ->withInput();
        }

        if (!$sameDay) {
            foreach ($endSlots as $slot) {
                if (!array_key_exists($slot, $slotOptions)) {
                    return redirect()
                        ->back()
                        ->withErrors(['end_slot' => 'Orario ultimo giorno non valido.'])
                        ->withInput();
                }
            }
        }

        $absence = Absence::create([
            'student_id' => $user->id,
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
            'time_from' => $startSlots,
            'time_to' => $sameDay ? null : $endSlots,
            'reason' => $validated['reason'],
            'status' => 'PENDING',
            'note' => $validated['note'] ?? null,
        ]);

        AuditLogger::log($user, 'absence.created', 'absence', $absence->id, [
            'reason' => $absence->reason,
            'status' => $absence->status,
            'date_from' => $absence->date_from?->format('Y-m-d'),
            'date_to' => $absence->date_to?->format('Y-m-d'),
            'time_from' => $absence->time_from,
            'time_to' => $absence->time_to,
        ]);

        $targetRole = $absence->requiredApproverRole();
        $approvalRecipients = $this->findApprovalRecipientsByRole($targetRole);
        $approvalsUrl = route('approvals.absences.index');
        $statusMessage = 'Assenza segnalata con successo.';

        if ($approvalRecipients->isNotEmpty()) {
            $sentRecipientEmails = [];
            $failedRecipientEmails = [];

            foreach ($approvalRecipients as $recipient) {
                try {
                    Mail::to($recipient->email)->send(new AbsenceApprovalRequestMail(
                        $absence,
                        $user,
                        $recipient,
                        $approvalsUrl,
                        $targetRole
                    ));

                    $sentRecipientEmails[] = $recipient->email;
                } catch (Throwable $exception) {
                    report($exception);

                    $failedRecipientEmails[] = [
                        'email' => $recipient->email,
                        'error' => Str::limit($exception->getMessage(), 300),
                    ];
                }
            }

            if (count($sentRecipientEmails) > 0) {
                AuditLogger::log($user, 'absence.approval_notification_sent', 'absence', $absence->id, [
                    'target_role' => $targetRole,
                    'recipient_emails' => $sentRecipientEmails,
                ]);
            }

            if (count($failedRecipientEmails) > 0) {
                AuditLogger::log($user, 'absence.approval_notification_failed', 'absence', $absence->id, [
                    'target_role' => $targetRole,
                    'failed_recipients' => $failedRecipientEmails,
                ]);

                $statusMessage .= ' Notifica email non inviata: credenziali SMTP non valide o server email non raggiungibile.';
            }
        } else {
            AuditLogger::log($user, 'absence.approval_notification_missing_recipients', 'absence', $absence->id, [
                'target_role' => $targetRole,
            ]);
        }

        return redirect()
            ->route('student.absences.index')
            ->with('status', $statusMessage);
    }

    public function show(Request $request, int $id): View
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('student.absences.access')) {
            abort(403);
        }

        $absence = Absence::query()
            ->where('id', $id)
            ->where('student_id', $user->id)
            ->with('certificates', 'signatureConfirmation')
            ->firstOrFail();

        $isAdult = $user->isAdult();
        $canGenerateSignatureLink = $isAdult === true;
        $signatureHint = null;

        if ($isAdult === null) {
            $signatureHint = 'Data di nascita mancante: impossibile determinare chi deve firmare.';
            $canGenerateSignatureLink = false;
        } elseif ($isAdult === false) {
            $signatureHint = 'Firma a carico del tutore legale.';
            $canGenerateSignatureLink = false;
        } else {
            $signatureHint = 'Firma a carico dello studente maggiorenne.';
        }

        return view('student.absences.show', [
            'absence' => $absence,
            'canGenerateSignatureLink' => $canGenerateSignatureLink,
            'signatureHint' => $signatureHint,
        ]);
    }


    public function uploadCertificate(Request $request, int $id, int $slot): RedirectResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('student.absences.access')) {
            abort(403);
        }

        if ($slot < 1 || $slot > 3) {
            abort(404);
        }

        $absence = Absence::query()
            ->where('id', $id)
            ->where('student_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'certificate_file' => ['required', 'file', 'mimes:pdf', 'max:4096'],
        ], [
            'certificate_file.required' => 'Carica un certificato PDF.',
            'certificate_file.mimes' => 'Il file deve essere un PDF.',
            'certificate_file.max' => 'Il file non puo superare 4MB.',
        ]);

        $existing = MedicalCertificate::query()
            ->where('absence_id', $absence->id)
            ->where('slot', $slot)
            ->first();

        if ($existing && $existing->file_path) {
            Storage::disk('public')->delete($existing->file_path);
        }

        $path = $validated['certificate_file']
            ->store('certificates/absences/' . $absence->id, 'public');

        $certificate = MedicalCertificate::updateOrCreate(
            ['absence_id' => $absence->id, 'slot' => $slot],
            [
                'file_path' => $path,
                'uploaded_at' => now(),
            ]
        );

        AuditLogger::log($user, 'absence.certificate_uploaded', 'absence', $absence->id, [
            'certificate_id' => $certificate->id,
            'slot' => $slot,
        ]);

        return redirect()
            ->route('student.absences.show', $absence->id)
            ->with('status', 'Certificato caricato con successo.');
    }

    public function downloadCertificate(Request $request, int $id, int $slot): Response
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('student.absences.access')) {
            abort(403);
        }

        if ($slot < 1 || $slot > 3) {
            abort(404);
        }

        $absence = Absence::query()
            ->where('id', $id)
            ->where('student_id', $user->id)
            ->firstOrFail();

        $certificate = MedicalCertificate::query()
            ->where('absence_id', $absence->id)
            ->where('slot', $slot)
            ->firstOrFail();

        $disk = Storage::disk('public');
        if (!$disk->exists($certificate->file_path)) {
            abort(404);
        }

        $absolutePath = $disk->path($certificate->file_path);
        return response()->file($absolutePath, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function generateSignatureLink(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('student.absences.access')) {
            abort(403);
        }

        $absence = Absence::query()
            ->where('id', $id)
            ->where('student_id', $user->id)
            ->firstOrFail();

        if ($absence->is_signed || $absence->status !== 'WAITING_SIGNATURE') {
            return redirect()
                ->route('student.absences.show', $absence->id)
                ->with('status', 'Assenza non firmabile.');
        }

        $isAdult = $user->isAdult();
        if ($isAdult === null) {
            return redirect()
                ->route('student.absences.show', $absence->id)
                ->with('status', 'Data di nascita mancante: impossibile determinare chi deve firmare.');
        }

        if (!$isAdult) {
            if (!$user->guardian || $user->guardian->role !== 'GUARDIAN') {
                return redirect()
                    ->route('student.absences.show', $absence->id)
                    ->with('status', 'Nessun tutore associato: impossibile generare il link firma.');
            }
        }

        if (!$user->email) {
            return redirect()
                ->route('student.absences.show', $absence->id)
                ->with('status', 'Email dello studente mancante: impossibile inviare il link firma.');
        }

        $token = Str::random(64);
        $tokenHash = hash('sha256', $token);
        $expiresAt = now()->addDays(7);

        SignatureConfirmation::updateOrCreate(
            ['absence_id' => $absence->id],
            [
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt,
                'signed_at' => null,
                'signature_path' => null,
                'signer_name' => null,
                'signer_email' => null,
                'ip_address' => null,
            ]
        );

        $link = route('public.absences.signature.show', $token);

        Mail::to($user->email)->send(new AbsenceSignatureLinkMail($absence, $user, $user, $link));

        AuditLogger::log($user, 'absence.signature_link_sent', 'absence', $absence->id, [
            'recipient_email' => $user->email,
            'recipient_role' => 'STUDENT',
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);

        return redirect()
            ->route('student.absences.show', $absence->id)
            ->with('signature_email', $user->email)
            ->with('status', 'Link firma inviato via email.');
    }

    public function downloadSignature(Request $request, int $id): Response
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('student.absences.access')) {
            abort(403);
        }

        $absence = Absence::query()
            ->where('id', $id)
            ->where('student_id', $user->id)
            ->firstOrFail();

        if (!$absence->signature_file_path) {
            abort(404);
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($absence->signature_file_path)) {
            abort(404);
        }

        $absolutePath = $disk->path($absence->signature_file_path);
        $mimeType = $disk->mimeType($absence->signature_file_path) ?: 'image/png';
        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
        ]);
    }

    private function reasonOptions(): array
    {
        return [
            'MALATTIA' => 'Malattia',
            'VISITA_MEDICA' => 'Visita medica',
            'IMPEGNO_FAMIGLIARE' => 'Impegno familiare',
            'MOTIVI_PERSONALI' => 'Motivi personali',
            'ALTRO' => 'Altro',
        ];
    }

    private function slotOptions(): array
    {
        return [
            '08:20-09:05' => '08:20-09:05',
            '09:05-09:50' => '09:05-09:50',
            '10:05-10:50' => '10:05-10:50',
            '10:50-11:35' => '10:50-11:35',
            '11:35-12:20' => '11:35-12:20',
            '12:30-13:15' => '12:30-13:15',
            '13:15-14:00' => '13:15-14:00',
            '14:00-14:45' => '14:00-14:45',
            '15:00-15:45' => '15:00-15:45',
            '15:45-16:30' => '15:45-16:30',
            '16:30-17:15' => '16:30-17:15',
        ];
    }

    private function isRequestAtLeast24HoursBeforeStart(string $dateFrom, array $startSlots): bool
    {
        if (count($startSlots) === 0) {
            return false;
        }

        $startTimes = [];
        foreach ($startSlots as $slot) {
            $parts = explode('-', $slot);
            if (count($parts) !== 2) {
                continue;
            }

            $startTimes[] = trim($parts[0]);
        }

        if (count($startTimes) === 0) {
            return false;
        }

        sort($startTimes);
        $absenceStart = Carbon::parse($dateFrom . ' ' . $startTimes[0]);
        return now()->addHours(24)->lessThanOrEqualTo($absenceStart);
    }

    private function findApprovalRecipientsByRole(string $targetRole)
    {
        return User::query()
            ->whereNotNull('email')
            ->where(function ($query) use ($targetRole) {
                $query->where('role', $targetRole)
                    ->orWhereHas('roles', function ($roleQuery) use ($targetRole) {
                        $roleQuery->where('name', $targetRole);
                    });
            })
            ->orderBy('name')
            ->get()
            ->unique('id')
            ->values();
    }
}
