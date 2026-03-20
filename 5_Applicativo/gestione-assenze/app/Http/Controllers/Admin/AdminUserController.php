<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesAdmin;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Controller per la gestione degli utenti da parte degli amministratori.
 * Permette di:
 * - visualizzare la lista utenti
 * - creare nuovi utenti
 * - modificare utenti esistenti
 * - eliminare utenti
 * - gestire ruoli e assegnazioni alle classi
 */
class AdminUserController extends Controller
{
    use AuthorizesAdmin;

    /**
     * Mostra la lista degli utenti con eventuali filtri di ricerca.
     */
    public function index(Request $request): View
    {
        // Controllo che l'utente autenticato sia amministratore
        $this->ensureAdmin($request->user());

        // Raccolta dei filtri provenienti dalla request
        $filters = [
            'q' => trim((string) $request->input('q', '')),
            'role' => strtoupper(trim((string) $request->input('role', ''))),
            'classroom_id' => (string) $request->input('classroom_id', ''),
            'status' => (string) $request->input('status', 'all'),
        ];

        // Query base degli utenti con relazioni utili
        $query = User::query()
            ->with('classroom', 'guardian')
            ->orderBy('name');

        // Filtro per nome o email
        if ($filters['q'] !== '') {
            $q = $filters['q'];
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%');
            });
        }

        // Filtro per ruolo
        if ($filters['role'] !== '') {
            $query->where('role', $filters['role']);
        }

        // Filtro per classe
        if ($filters['classroom_id'] !== '') {
            $query->where('classroom_id', (int) $filters['classroom_id']);
        }

        // Filtro stato account
        if ($filters['status'] === 'active') {
            $query->where(function ($statusQuery) {
                $statusQuery->whereNull('is_active')
                    ->orWhere('is_active', true);
            });
        } elseif ($filters['status'] === 'inactive') {
            $query->where('is_active', false);
        }

        return view('admin.users.index', [
            'users' => $query->paginate(20)->withQueryString(),
            'filters' => $filters,
            'roles' => Role::query()->orderBy('name')->get(),
            'classrooms' => Classroom::query()->orderBy('year')->orderBy('section')->orderBy('name')->get(),
        ]);
    }

    /**
     * Mostra il form per la creazione di un nuovo utente.
     */
    public function create(Request $request): View
    {
        $this->ensureAdmin($request->user());

        return view('admin.users.create', $this->formData());
    }

    /**
     * Salva un nuovo utente nel database.
     */
    public function store(Request $request): RedirectResponse
    {
        $admin = $request->user();
        $this->ensureAdmin($admin);

        // Validazione dei dati inviati dal form
        $validated = $this->validatePayload($request);

        // Creazione dell'utente
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],

            // La password viene salvata come hash per sicurezza
            'password_hash' => Hash::make($validated['password']),

            'role' => $validated['role'],
            'classroom_id' => $validated['classroom_id'] ?? null,
            'guardian_id' => $validated['guardian_id'] ?? null,
            'is_minor' => (bool) ($validated['is_minor'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'description' => $validated['description'] ?? null,
        ]);

        // Sincronizzazione del ruolo nella tabella pivot
        $this->syncRolePivot($user, $validated['role']);

        // Sincronizzazione delle classi insegnate (solo per ruoli docenti)
        $this->syncTeachingAssignments($user, $validated['teaching_classroom_ids'] ?? []);

        // Registrazione nei log di audit
        AuditLogger::log($admin, 'admin.user.created', 'user', $user->id, [
            'role' => $user->role,
            'is_active' => $user->is_active,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Utente creato con successo.');
    }

    /**
     * Mostra il form per modificare un utente esistente.
     */
    public function edit(Request $request, int $id): View
    {
        $this->ensureAdmin($request->user());

        // Recupera utente con ruoli e classi insegnate
        $user = User::query()
            ->with('roles', 'taughtClassrooms')
            ->findOrFail($id);

        $formData = $this->formData();
        $formData['targetUser'] = $user;

        return view('admin.users.edit', $formData);
    }

    /**
     * Aggiorna i dati di un utente esistente.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $admin = $request->user();
        $this->ensureAdmin($admin);

        $targetUser = User::findOrFail($id);

        $validated = $this->validatePayload($request, $targetUser->id, false);

        // Aggiorna i dati base dell'utente
        $targetUser->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'classroom_id' => $validated['classroom_id'] ?? null,
            'guardian_id' => $validated['guardian_id'] ?? null,
            'is_minor' => (bool) ($validated['is_minor'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'description' => $validated['description'] ?? null,
        ]);

        // Se viene inserita una nuova password viene aggiornata
        if (!empty($validated['password'])) {
            $targetUser->password_hash = Hash::make($validated['password']);
        }

        // Impedisce all'admin di disattivare il proprio account
        if ($targetUser->id === $admin->id && !$targetUser->is_active) {
            return redirect()
                ->back()
                ->withInput()
                ->with('status', 'Non puoi disattivare il tuo account.');
        }

        $targetUser->save();

        $this->syncRolePivot($targetUser, $validated['role']);
        $this->syncTeachingAssignments($targetUser, $validated['teaching_classroom_ids'] ?? []);

        AuditLogger::log($admin, 'admin.user.updated', 'user', $targetUser->id, [
            'role' => $targetUser->role,
            'is_active' => $targetUser->is_active,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Utente aggiornato con successo.');
    }

    /**
     * Elimina un utente dal sistema.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $admin = $request->user();
        $this->ensureAdmin($admin);

        $targetUser = User::findOrFail($id);

        // Impedisce all'admin di eliminare il proprio account
        if ($targetUser->id === $admin->id) {
            return redirect()
                ->route('admin.users.index')
                ->with('status', 'Non puoi eliminare il tuo account.');
        }

        $targetUserId = $targetUser->id;

        try {
            $targetUser->delete();
        } catch (\Throwable $exception) {

            // Se esistono relazioni collegate impedisce l'eliminazione
            report($exception);
            return redirect()
                ->route('admin.users.index')
                ->with('status', 'Impossibile eliminare utente: esistono dati collegati. Disattiva l\'account invece.');
        }

        // Registrazione nel log di audit
        AuditLogger::log($admin, 'admin.user.deleted', 'user', $targetUserId);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Utente eliminato con successo.');
    }

    /**
     * Recupera i dati necessari per i form utenti
     * (ruoli, classi e tutori disponibili).
     */
    private function formData(): array
    {
        return [
            'roles' => Role::query()->orderBy('name')->get(),
            'classrooms' => Classroom::query()->orderBy('year')->orderBy('section')->orderBy('name')->get(),
            'guardians' => User::query()
                ->where('role', 'GUARDIAN')
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ];
    }

    /**
     * Valida i dati inviati nei form di creazione/modifica utente.
     */
    private function validatePayload(Request $request, ?int $ignoreUserId = null, bool $requirePassword = true): array
    {
        $passwordRules = $requirePassword
            ? ['required', 'string', 'min:10']
            : ['nullable', 'string', 'min:10'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('user', 'email')->ignore($ignoreUserId)],
            'password' => $passwordRules,
            'role' => ['required', 'string', Rule::in(['STUDENT', 'TEACHER', 'GUARDIAN', 'CAPOLAB', 'DIREZIONE', 'ADMIN'])],
            'classroom_id' => ['nullable', 'integer', 'exists:classroom,id'],
            'guardian_id' => ['nullable', 'integer', 'exists:user,id'],
            'is_minor' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:2000'],
            'teaching_classroom_ids' => ['nullable', 'array'],
            'teaching_classroom_ids.*' => ['integer', 'exists:classroom,id'],
        ]);

        // Controllo aggiuntivo: il guardian selezionato deve avere ruolo GUARDIAN
        if (!empty($validated['guardian_id'])) {
            $guardianRole = User::query()->where('id', (int) $validated['guardian_id'])->value('role');
            if ($guardianRole !== 'GUARDIAN') {
                throw ValidationException::withMessages([
                    'guardian_id' => 'Il tutore selezionato non ha ruolo GUARDIAN.',
                ]);
            }
        }

        return $validated;
    }

    /**
     * Sincronizza il ruolo dell'utente nella tabella pivot role_user.
     */
    private function syncRolePivot(User $user, string $roleName): void
    {
        if ($this->roleUserPivotPointsToUsersTable()) {
            return;
        }

        $roleId = Role::query()->where('name', $roleName)->value('id');
        if (!$roleId) {
            return;
        }

        $user->roles()->sync([$roleId]);
    }

    /**
     * Verifica a quale tabella punta la foreign key della pivot role_user.
     * Serve per compatibilità con diverse strutture di database.
     */
    private function roleUserPivotPointsToUsersTable(): bool
    {
        $fkTarget = DB::table('information_schema.key_column_usage')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', 'role_user')
            ->where('column_name', 'user_id')
            ->whereNotNull('referenced_table_name')
            ->value('referenced_table_name');

        return $fkTarget === 'users';
    }

    /**
     * Sincronizza le classi insegnate da un docente.
     * Se l'utente non è docente, le assegnazioni vengono rimosse.
     */
    private function syncTeachingAssignments(User $user, array $classroomIds): void
    {
        if (!in_array($user->role, ['TEACHER', 'CAPOLAB', 'DIREZIONE', 'ADMIN'], true)) {
            $user->taughtClassrooms()->sync([]);
            return;
        }

        $ids = array_map(static fn ($id) => (int) $id, $classroomIds);
        $ids = array_values(array_unique(array_filter($ids, static fn ($id) => $id > 0)));

        $user->taughtClassrooms()->sync($ids);
    }
}