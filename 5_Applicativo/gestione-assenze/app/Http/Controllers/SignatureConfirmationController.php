<?php

namespace App\Http\Controllers;

use App\Models\SignatureConfirmation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Support\AuditLogger;

/**
 * Gestisce il flusso pubblico di firma delle assenze tramite link con token.
 * Consente visualizzazione stato richiesta e registrazione della firma grafica.
 */
class SignatureConfirmationController extends Controller
{
    /**
     * Mostra la pagina di firma associata al token ricevuto.
     * Rende una vista con stato: invalid, signed, expired o ready.
     */
    public function show(Request $request, string $token): View
    {
        // Il token salvato a DB è hashato: si confronta solo l'hash SHA-256.
        $tokenHash = hash('sha256', $token);

        // Carica conferma firma ed entità collegate necessarie alla pagina.
        $confirmation = SignatureConfirmation::query()
            ->where('token_hash', $tokenHash)
            ->with('absence.student')
            ->first();

        // Token non riconosciuto.
        if (!$confirmation) {
            return view('public.absence-signature', [
                'status' => 'invalid',
            ]);
        }

        $absence = $confirmation->absence;

        // Se l'assenza non è più in attesa firma, il link non è utilizzabile.
        if ($absence && !$absence->is_signed && $absence->status !== 'WAITING_SIGNATURE') {
            return view('public.absence-signature', [
                'status' => 'invalid',
            ]);
        }

        // Firma già registrata per questa conferma.
        if ($confirmation->signed_at) {
            return view('public.absence-signature', [
                'status' => 'signed',
                'confirmation' => $confirmation,
            ]);
        }

        // Link scaduto in base alla data di expiration.
        if ($confirmation->expires_at && $confirmation->expires_at->isPast()) {
            return view('public.absence-signature', [
                'status' => 'expired',
                'confirmation' => $confirmation,
            ]);
        }

        // Link valido e pronto alla raccolta della firma.
        return view('public.absence-signature', [
            'status' => 'ready',
            'confirmation' => $confirmation,
            'token' => $token,
        ]);
    }

    /**
     * Valida e salva la firma grafica associata al token.
     * Aggiorna sia la conferma di firma sia l'assenza collegata.
     */
    public function store(Request $request, string $token): RedirectResponse
    {
        // Stesso meccanismo di ricerca hashato usato nella show().
        $tokenHash = hash('sha256', $token);

        $confirmation = SignatureConfirmation::query()
            ->where('token_hash', $tokenHash)
            ->with('absence')
            ->first();

        // Blocca invii con token inesistente.
        if (!$confirmation) {
            return redirect()
                ->back()
                ->withErrors(['signature_data' => 'Link firma non valido.']);
        }

        $absence = $confirmation->absence;

        // Blocca invii se l'assenza non è più firmabile.
        if ($absence && !$absence->is_signed && $absence->status !== 'WAITING_SIGNATURE') {
            return redirect()
                ->back()
                ->withErrors(['signature_data' => 'La firma non e piu disponibile.']);
        }

        // Evita doppie firme sullo stesso token.
        if ($confirmation->signed_at) {
            return redirect()
                ->back()
                ->withErrors(['signature_data' => 'Questa assenza risulta gia firmata.']);
        }

        // Verifica scadenza del link.
        if ($confirmation->expires_at && $confirmation->expires_at->isPast()) {
            return redirect()
                ->back()
                ->withErrors(['signature_data' => 'Il link firma e scaduto.']);
        }

        // Valida i dati del firmatario e la firma codificata in base64.
        $validated = $request->validate([
            'signer_name' => ['required', 'string', 'max:100'],
            'signer_email' => ['nullable', 'email', 'max:150'],
            'signature_data' => ['required', 'string'],
        ], [
            'signer_name.required' => 'Inserisci il nome di chi firma.',
            'signature_data.required' => 'La firma e obbligatoria.',
        ]);

        $dataUrl = $validated['signature_data'];
        $prefix = 'data:image/png;base64,';

        // Accetta solo immagini PNG in data URL.
        if (!str_starts_with($dataUrl, $prefix)) {
            return redirect()
                ->back()
                ->withErrors(['signature_data' => 'Formato firma non valido.']);
        }

        // Decodifica il payload base64 della firma.
        $decoded = base64_decode(substr($dataUrl, strlen($prefix)));
        if ($decoded === false) {
            return redirect()
                ->back()
                ->withErrors(['signature_data' => 'Impossibile leggere la firma.']);
        }

        // Genera percorso univoco e salva l'immagine sul disco pubblico.
        $path = 'signatures/absences/' . $confirmation->absence_id . '/signature_' . now()->format('Ymd_His') . '_' . Str::random(6) . '.png';
        Storage::disk('public')->put($path, $decoded);

        // Aggiorna i dati della conferma firma.
        $confirmation->signer_name = $validated['signer_name'];
        $confirmation->signer_email = $validated['signer_email'] ?? null;
        $confirmation->signature_path = $path;
        $confirmation->signed_at = now();
        $confirmation->ip_address = $request->ip();
        $confirmation->save();

        if ($absence) {
            // Sincronizza lo stato dell'assenza con l'avvenuta firma.
            $absence->is_signed = true;
            $absence->signed_at = $confirmation->signed_at;
            $absence->signed_by_user_id = null;
            $absence->signature_file_path = $path;
            $absence->save();

            // Traccia l'evento in audit log per finalità di tracciabilità.
            AuditLogger::log(null, 'absence.signed', 'absence', $absence->id, [
                'signer_name' => $confirmation->signer_name,
                'signer_email' => $confirmation->signer_email,
                'ip_address' => $confirmation->ip_address,
                'signed_at' => optional($confirmation->signed_at)->format('Y-m-d H:i:s'),
            ]);
        }

        return redirect()
            ->back()
            ->with('status', 'Firma registrata con successo.');
    }
}
