<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\MedicalCertificate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class StudentAbsenceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user || $user->role !== 'STUDENT') {
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
        if (!$user || $user->role !== 'STUDENT') {
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
        if (!$user || $user->role !== 'STUDENT') {
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

        Absence::create([
            'student_id' => $user->id,
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
            'time_from' => $startSlots,
            'time_to' => $sameDay ? null : $endSlots,
            'reason' => $validated['reason'],
            'status' => 'PENDING',
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()
            ->route('student.absences.index')
            ->with('status', 'Assenza segnalata con successo.');
    }

    public function show(Request $request, int $id): View
    {
        $user = $request->user();
        if (!$user || $user->role !== 'STUDENT') {
            abort(403);
        }

        $absence = Absence::query()
            ->where('id', $id)
            ->where('student_id', $user->id)
            ->with('certificates')
            ->firstOrFail();

        return view('student.absences.show', [
            'absence' => $absence,
        ]);
    }


    public function uploadCertificate(Request $request, int $id, int $slot): RedirectResponse
    {
        $user = $request->user();
        if (!$user || $user->role !== 'STUDENT') {
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

        MedicalCertificate::updateOrCreate(
            ['absence_id' => $absence->id, 'slot' => $slot],
            [
                'file_path' => $path,
                'uploaded_at' => now(),
            ]
        );

        return redirect()
            ->route('student.absences.show', $absence->id)
            ->with('status', 'Certificato caricato con successo.');
    }

    public function downloadCertificate(Request $request, int $id, int $slot): Response
    {
        $user = $request->user();
        if (!$user || $user->role !== 'STUDENT') {
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

    public function sign(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$user || $user->role !== 'STUDENT') {
            abort(403);
        }

        $absence = Absence::query()
            ->where('id', $id)
            ->where('student_id', $user->id)
            ->firstOrFail();

        if (!$absence->is_signed && $absence->status !== 'WAITING_SIGNATURE') {
            return redirect()
                ->route('student.absences.index', $request->only(['status', 'date_from', 'date_to', 'page']))
                ->with('status', 'Assenza non firmabile.');
        }

        $validated = $request->validate([
            'signature_file' => ['required', 'file', 'mimes:pdf', 'max:4096'],
        ], [
            'signature_file.required' => 'Carica un file PDF per firmare.',
            'signature_file.mimes' => 'Il file deve essere un PDF.',
            'signature_file.max' => 'Il file non puo superare 4MB.',
        ]);

        if ($absence->signature_file_path) {
            Storage::disk('public')->delete($absence->signature_file_path);
        }

        $path = $validated['signature_file']->store('signatures/absences', 'public');

        $absence->is_signed = true;
        $absence->signed_at = now();
        $absence->signed_by_user_id = $user->id;
        $absence->signature_file_path = $path;
        $absence->save();

        return redirect()
            ->route('student.absences.index', $request->only(['status', 'date_from', 'date_to', 'page']))
            ->with('status', $absence->wasChanged('signature_file_path') ? 'PDF firma aggiornato.' : 'Assenza firmata con successo.');
    }

    public function downloadSignature(Request $request, int $id): Response
    {
        $user = $request->user();
        if (!$user || $user->role !== 'STUDENT') {
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
        return response()->file($absolutePath, [
            'Content-Type' => 'application/pdf',
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
}
