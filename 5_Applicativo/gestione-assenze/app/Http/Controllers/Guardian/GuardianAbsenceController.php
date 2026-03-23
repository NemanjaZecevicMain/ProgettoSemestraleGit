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
use App\Support\AuditLogger;

/**
 * Controller per la gestione assenze lato tutore.
 * Permette consultazione assenze dello studente associato e gestione firma.
 */
class GuardianAbsenceController extends Controller
{
    /**
     * Mostra elenco assenze degli studenti associati al tutore autenticato.
     */
    public function index(Request $request): View
    {
        // Accesso riservato a utenti con permesso guardian dedicato.
        $user = $request->user();
        if (!$user || !$user->hasPermission('guardian.absences.access')) {
            abort(403);
        }

        // Filtri data opzionali da query string.
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Query base: assenze degli studenti che hanno questo utente come tutore.
        $query = Absence::query()
            ->whereHas('student', function ($query) use ($user) {
                $query->where('guardian_id', $user->id);
            })
            ->with('student')
            ->orderByDesc('date_from');

        // Applica i filtri se presenti.
        if ($dateFrom) {
            $query->whereDate('date_from', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('date_to', '<=', $dateTo);
        }

        // Paginazione con conservazione dei filtri correnti.
        $absences = $query->paginate(10)->withQueryString();

        return view('guardian.absences.index', [
            'absences' => $absences,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * Mostra il dettaglio di una singola assenza visibile al tutore.
     */
    public function show(Request $request, int $id): View
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('guardian.absences.access')) {
            abort(403);
        }

        // Recupera assenza solo se appartiene a studente associato al tutore.
        $absence = Absence::query()
            ->where('id', $id)
            ->whereHas('student', function ($query) use ($user) {
                $query->where('guardian_id', $user->id);
            })
            ->with('certificates', 'signatureConfirmation', 'student')
            ->firstOrFail();

        // Determina se la firma e a carico studente (maggiorenne) o tutore.
        $studentIsAdult = $absence->student?->isAdult();

        return view('guardian.absences.show', [
            'absence' => $absence,
            'studentIsAdult' => $studentIsAdult,
        ]);
    }

    /**
     * Genera e invia via email il link pubblico per la firma dell'assenza.
     */
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

        // Link generabile solo per assenze in attesa firma e non gia firmate.
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

        // Se studente maggiorenne, la firma non puo essere gestita dal tutore.
        if ($studentIsAdult) {
            return redirect()
                ->route('guardian.absences.show', $absence->id)
                ->with('status', 'Lo studente e maggiorenne: la firma e a suo carico.');
        }

        if (!$user->email) {
            return redirect()
                ->route('guardian.absences.show', $absence->id)
                ->with('status', 'Email del tutore mancante: impossibile inviare il link firma.');
        }

        // Crea token casuale, salva solo hash e imposta scadenza 7 giorni.
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

        // Invia email al tutore con il link di firma.
        Mail::to($user->email)->send(new AbsenceSignatureLinkMail($absence, $absence->student, $user, $link));

        // Registra evento per tracciabilita.
        AuditLogger::log($user, 'absence.signature_link_sent', 'absence', $absence->id, [
            'recipient_email' => $user->email,
            'recipient_role' => 'GUARDIAN',
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);

        return redirect()
            ->route('guardian.absences.show', $absence->id)
            ->with('signature_email', $user->email)
            ->with('status', 'Link firma inviato via email.');
    }

    /**
     * Scarica il file della firma associata all'assenza.
     */
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

        // Se manca il path firma o il file fisico, ritorna 404.
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

    /**
     * Scarica il certificato medico PDF associato ad assenza e slot.
     */
    public function downloadCertificate(Request $request, int $id, int $slot): Response
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('guardian.absences.access')) {
            abort(403);
        }

        // Slot valido da 1 a 3.
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
