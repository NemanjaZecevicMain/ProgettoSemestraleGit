<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesAdmin;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller che gestisce l'associazione tra ruoli e permessi
 * nell'area amministrativa.
 * Permette agli admin di visualizzare i ruoli disponibili
 * e aggiornare i permessi assegnati a ciascun ruolo.
 */
class AdminRolePermissionController extends Controller
{
    use AuthorizesAdmin;
 
    /**
     * Mostra la pagina di gestione ruoli-permessi.
     * Recupera tutti i ruoli con i loro permessi associati
     * e l'elenco completo dei permessi disponibili.
     */
    public function index(Request $request): View
    {
        // Verifica che l'utente autenticato sia un amministratore
        $this->ensureAdmin($request->user());

        $allowedRoles = $this->allowedRoleNames();

        return view('admin.roles-permissions.index', [
            // Recupera tutti i ruoli ordinati per nome
            // con i relativi permessi associati
            'roles' => Role::query()
                ->with('permissions:id')
                ->whereIn('name', $allowedRoles)
                ->orderBy('name')
                ->get(),

            // Recupera la lista completa dei permessi disponibili
            'permissions' => Permission::query()->orderBy('label')->orderBy('name')->get(),
        ]);
    }

    /**
     * Aggiorna i permessi associati a uno specifico ruolo.
     * I permessi vengono sincronizzati con quelli inviati dal form.
     */
    public function update(Request $request, int $roleId): RedirectResponse
    {
        $admin = $request->user();

        // Controllo che l'utente sia un amministratore
        $this->ensureAdmin($admin);

        // Recupera il ruolo dal database oppure genera errore se non esiste
        $role = Role::query()
            ->whereIn('name', $this->allowedRoleNames())
            ->findOrFail($roleId);

        // Validazione dei dati ricevuti dal form
        $validated = $request->validate([
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        // Se non vengono inviati permessi, viene utilizzato un array vuoto
        $permissionIds = $validated['permission_ids'] ?? [];

        // Sincronizza i permessi del ruolo:
        // - aggiunge quelli nuovi
        // - rimuove quelli non presenti nell'array
        $role->permissions()->sync($permissionIds);

        // Registra l'operazione nei log di audit
        AuditLogger::log($admin, 'admin.role.permissions.updated', 'role', $role->id, [
            'role' => $role->name,
            'permission_ids' => $permissionIds,
        ]);

        // Reindirizza alla pagina di gestione con messaggio di conferma
        return redirect()
            ->route('admin.roles-permissions.index')
            ->with('status', 'Permessi aggiornati per il ruolo ' . $role->name . '.');
    }

    /**
     * Ruoli effettivamente utilizzati dal sistema.
     */
    private function allowedRoleNames(): array
    {
        return ['ADMIN', 'CAPOLAB', 'DIREZIONE', 'GUARDIAN', 'STUDENT', 'TEACHER'];
    }
}
