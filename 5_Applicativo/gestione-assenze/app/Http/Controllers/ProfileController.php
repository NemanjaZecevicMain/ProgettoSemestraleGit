<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Gestisce le operazioni legate al profilo dell'utente autenticato.
 * Include visualizzazione, aggiornamento dati e cancellazione account.
 */
class ProfileController extends Controller
{
    /**
     * Mostra il form profilo precompilato con i dati dell'utente corrente.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Aggiorna le informazioni del profilo tramite richiesta validata.
     * Se l'email cambia, invalida la verifica email per richiederla nuovamente.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Applica solo i campi consentiti dal FormRequest.
        $request->user()->fill($request->validated());

        // Se l'email è stata modificata, va riconfermata.
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Persiste le modifiche sul database.
        $request->user()->save();

        // Torna al form con messaggio di stato per il frontend.
        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Elimina definitivamente l'account dell'utente autenticato.
     * Richiede la password corrente come conferma dell'operazione.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Validazione nel bag dedicato per mostrare errori nel modal/form di eliminazione.
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Chiude la sessione prima della rimozione del record utente.
        Auth::logout();

        $user->delete();

        // Invalida la sessione e rigenera il token CSRF per sicurezza.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
