<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('settings.index');
    }

    public function updateDescription(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'description.max' => 'La descrizione non può superare 500 caratteri.',
        ]);

        $user = $request->user();
        $user->description = $validated['description'] ?? null;
        $user->save();

        return back()->with('status', 'Descrizione aggiornata.');
    }
}
