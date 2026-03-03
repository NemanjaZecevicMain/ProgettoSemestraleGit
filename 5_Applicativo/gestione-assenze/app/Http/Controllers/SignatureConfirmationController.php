<?php

namespace App\Http\Controllers;

use App\Models\SignatureConfirmation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SignatureConfirmationController extends Controller
{
    public function show(Request $request, string $token): View
    {
        $tokenHash = hash('sha256', $token);

        $confirmation = SignatureConfirmation::query()
            ->where('token_hash', $tokenHash)
            ->with('absence.student')
            ->first();

        if (!$confirmation) {
            return view('public.absence-signature', [
                'status' => 'invalid',
            ]);
        }

        $absence = $confirmation->absence;
        if ($absence && !$absence->is_signed && $absence->status !== 'WAITING_SIGNATURE') {
            return view('public.absence-signature', [
                'status' => 'invalid',
            ]);
        }

        if ($confirmation->signed_at) {
            return view('public.absence-signature', [
                'status' => 'signed',
                'confirmation' => $confirmation,
            ]);
        }

        if ($confirmation->expires_at && $confirmation->expires_at->isPast()) {
            return view('public.absence-signature', [
                'status' => 'expired',
                'confirmation' => $confirmation,
            ]);
        }

        return view('public.absence-signature', [
            'status' => 'ready',
            'confirmation' => $confirmation,
            'token' => $token,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $tokenHash = hash('sha256', $token);

        $confirmation = SignatureConfirmation::query()
            ->where('token_hash', $tokenHash)
            ->with('absence')
            ->first();

        if (!$confirmation) {
            return redirect()
                ->back()
                ->withErrors(['signature_data' => 'Link firma non valido.']);
        }

        $absence = $confirmation->absence;
        if ($absence && !$absence->is_signed && $absence->status !== 'WAITING_SIGNATURE') {
            return redirect()
                ->back()
                ->withErrors(['signature_data' => 'La firma non e piu disponibile.']);
        }

        if ($confirmation->signed_at) {
            return redirect()
                ->back()
                ->withErrors(['signature_data' => 'Questa assenza risulta gia firmata.']);
        }

        if ($confirmation->expires_at && $confirmation->expires_at->isPast()) {
            return redirect()
                ->back()
                ->withErrors(['signature_data' => 'Il link firma e scaduto.']);
        }

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
        if (!str_starts_with($dataUrl, $prefix)) {
            return redirect()
                ->back()
                ->withErrors(['signature_data' => 'Formato firma non valido.']);
        }

        $decoded = base64_decode(substr($dataUrl, strlen($prefix)));
        if ($decoded === false) {
            return redirect()
                ->back()
                ->withErrors(['signature_data' => 'Impossibile leggere la firma.']);
        }

        $path = 'signatures/absences/' . $confirmation->absence_id . '/signature_' . now()->format('Ymd_His') . '_' . Str::random(6) . '.png';
        Storage::disk('public')->put($path, $decoded);

        $confirmation->signer_name = $validated['signer_name'];
        $confirmation->signer_email = $validated['signer_email'] ?? null;
        $confirmation->signature_path = $path;
        $confirmation->signed_at = now();
        $confirmation->ip_address = $request->ip();
        $confirmation->save();

        if ($absence) {
            $absence->is_signed = true;
            $absence->signed_at = $confirmation->signed_at;
            $absence->signed_by_user_id = null;
            $absence->signature_file_path = $path;
            $absence->save();
        }

        return redirect()
            ->back()
            ->with('status', 'Firma registrata con successo.');
    }
}
