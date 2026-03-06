<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\SignatureConfirmation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use App\Models\MedicalCertificate;
use App\Mail\AbsenceSignatureLinkMail;
use Illuminate\Support\Facades\Mail;

class GuardianAbsenceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('guardian.absences.access')) {
            abort(403);
        }

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Absence::query()
            ->whereHas('student', function ($query) use ($user) {
                $query->where('guardian_id', $user->id);
            })
            ->with('student')
            ->orderByDesc('date_from');

        if ($dateFrom) {
            $query->whereDate('date_from', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('date_to', '<=', $dateTo);
        }

        $absences = $query->paginate(10)->withQueryString();

        return view('guardian.absences.index', [
            'absences' => $absences,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('guardian.absences.access')) {
            abort(403);
        }

        $absence = Absence::query()
            ->where('id', $id)
            ->whereHas('student', function ($query) use ($user) {
                $query->where('guardian_id', $user->id);
            })
            ->with('certificates', 'signatureConfirmation', 'student')
            ->firstOrFail();

        $studentIsAdult = $absence->student?->isAdult();

        return view('guardian.absences.show', [
            'absence' => $absence,
            'studentIsAdult' => $studentIsAdult,
        ]);
    }

    public function generateSignatureLink(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('guardian.absences.access')) {
            abort(403);
        }

        $absence = Absence::query()
            ->where('id', $id)
            ->whereHas('student', function ($query) use ($user) {
                $query->where('guardian_id', $user->id);
            })
            ->with('student')
            ->firstOrFail();

        if ($absence->is_signed || $absence->status !== 'WAITING_SIGNATURE') {
            return redirect()
                ->route('guardian.absences.show', $absence->id)
                ->with('status', 'Assenza non firmabile.');
        }

        $studentIsAdult = $absence->student?->isAdult();
        if ($studentIsAdult === null) {
            return redirect()
                ->route('guardian.absences.show', $absence->id)
                ->with('status', 'Data di nascita mancante: impossibile determinare chi deve firmare.');
        }

        if ($studentIsAdult) {
            return redirect()
                ->route('guardian.absences.show', $absence->id)
                ->with('status', 'Lo studente è maggiorenne: la firma è a suo carico.');
        }

        if (!$user->email) {
            return redirect()
                ->route('guardian.absences.show', $absence->id)
                ->with('status', 'Email del tutore mancante: impossibile inviare il link firma.');
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

        Mail::to($user->email)->send(new AbsenceSignatureLinkMail($absence, $absence->student, $user, $link));

        return redirect()
            ->route('guardian.absences.show', $absence->id)
            ->with('signature_email', $user->email)
            ->with('status', 'Link firma inviato via email.');
    }

    public function downloadSignature(Request $request, int $id): Response
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('guardian.absences.access')) {
            abort(403);
        }

        $absence = Absence::query()
            ->where('id', $id)
            ->whereHas('student', function ($query) use ($user) {
                $query->where('guardian_id', $user->id);
            })
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

    public function downloadCertificate(Request $request, int $id, int $slot): Response
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('guardian.absences.access')) {
            abort(403);
        }

        if ($slot < 1 || $slot > 3) {
            abort(404);
        }

        $absence = Absence::query()
            ->where('id', $id)
            ->whereHas('student', function ($query) use ($user) {
                $query->where('guardian_id', $user->id);
            })
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
}
