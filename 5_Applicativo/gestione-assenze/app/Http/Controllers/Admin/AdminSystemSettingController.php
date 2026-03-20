<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesAdmin;
use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Controller che gestisce le configurazioni di sistema
 * accessibili dall'area amministrativa.
 * Permette agli admin di visualizzare e aggiornare alcune
 * impostazioni globali salvate nel database.
 */
class AdminSystemSettingController extends Controller
{
    use AuthorizesAdmin;

    /**
     * Mostra la pagina delle configurazioni di sistema.
     * Recupera le impostazioni attuali tramite il metodo currentSettings().
     */
    public function index(Request $request): View
    {
        // Verifica che l'utente autenticato sia un amministratore
        $this->ensureAdmin($request->user());

        return view('admin.settings.index', [
            // Passa alla view le impostazioni correnti
            'settings' => $this->currentSettings(),
        ]);
    }

    /**
     * Aggiorna le configurazioni di sistema salvate nel database.
     * I valori vengono validati e poi salvati nella tabella system_settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $admin = $request->user();

        // Controllo che l'utente sia amministratore
        $this->ensureAdmin($admin);

        // Verifica che la tabella system_settings esista
        // (utile se le migration non sono ancora state eseguite)
        if (!Schema::hasTable('system_settings')) {
            return redirect()
                ->route('admin.settings.index')
                ->with('status', 'Tabella system_settings non trovata. Esegui prima le migration.');
        }

        // Validazione dei dati inviati dal form
        $validated = $request->validate([
            'institute_name' => ['required', 'string', 'max:255'],
            'absence_signature_expiry_days' => ['required', 'integer', 'min:1', 'max:60'],
            'monthly_report_deadline_day' => ['required', 'integer', 'min:1', 'max:31'],
            'default_guardian_notification_email' => ['nullable', 'email', 'max:255'],
        ]);

        // Salvataggio delle impostazioni nel database
        // updateOrCreate aggiorna il valore se la chiave esiste,
        // altrimenti crea una nuova entry
        foreach ($validated as $key => $value) {
            SystemSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value === null ? null : (string) $value]
            );
        }

        // Registrazione dell'operazione nei log di audit
        AuditLogger::log($admin, 'admin.settings.updated', 'system_settings', null, $validated);

        // Redirect alla pagina delle impostazioni con messaggio di conferma
        return redirect()
            ->route('admin.settings.index')
            ->with('status', 'Configurazioni aggiornate.');
    }

    /**
     * Recupera le impostazioni correnti del sistema.
     * Se la tabella non esiste o alcuni valori non sono salvati,
     * vengono utilizzati valori di default.
     */
    private function currentSettings(): array
    {
        // Se la tabella non esiste restituisce valori di default
        if (!Schema::hasTable('system_settings')) {
            return [
                'institute_name' => config('app.name', 'CPT Lugano-Trevano'),
                'absence_signature_expiry_days' => 7,
                'monthly_report_deadline_day' => 5,
                'default_guardian_notification_email' => '',
            ];
        }

        // Recupera tutte le impostazioni salvate nel database
        // sotto forma di coppie chiave → valore
        $saved = SystemSetting::query()->pluck('value', 'key');

        // Restituisce le impostazioni finali usando:
        // - valore salvato nel DB se presente
        // - valore di default altrimenti
        return [
            'institute_name' => $saved['institute_name'] ?? config('app.name', 'CPT Lugano-Trevano'),
            'absence_signature_expiry_days' => (int) ($saved['absence_signature_expiry_days'] ?? 7),
            'monthly_report_deadline_day' => (int) ($saved['monthly_report_deadline_day'] ?? 5),
            'default_guardian_notification_email' => $saved['default_guardian_notification_email'] ?? '',
        ];
    }
}