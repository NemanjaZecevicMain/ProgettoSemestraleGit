<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestisce la pagina impostazioni dell'utente e l'aggiornamento
 * di preferenze/dati modificabili da interfaccia.
 */
class SettingsController extends Controller
{
    /**
     * Mostra la schermata principale delle impostazioni.
     */
    public function index(): View
    {
        return view('settings.index');
    }

    /**
     * Aggiorna la descrizione profilo dell'utente autenticato.
     * La descrizione è opzionale e limitata a 500 caratteri.
     */
    public function updateDescription(Request $request): RedirectResponse
    {
        // Valida input e messaggi personalizzati di errore.
        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'description.max' => 'La descrizione non puo superare 500 caratteri.',
        ]);

        $user = $request->user();

        // Salva la descrizione oppure null quando non valorizzata.
        $user->description = $validated['description'] ?? null;
        $user->save();

        return back()->with('status', 'Descrizione aggiornata.');
    }
}
