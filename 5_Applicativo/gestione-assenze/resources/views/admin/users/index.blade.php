<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Gestione utenti</h1>
                <p class="text-sm text-slate-500">Modifica, assegnazioni e stato account.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-5">
                <div>
                    <label for="q" class="mb-1 block text-xs font-medium text-slate-600">Nome o email</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900" />
                </div>
                <div>
                    <label for="role" class="mb-1 block text-xs font-medium text-slate-600">Ruolo</label>
                    <select id="role" name="role" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
                        <option value="">Tutti</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected($filters['role'] === $role->name)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="classroom_id" class="mb-1 block text-xs font-medium text-slate-600">Classe</label>
                    <select id="classroom_id" name="classroom_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
                        <option value="">Tutte</option>
                        @foreach ($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" @selected($filters['classroom_id'] == (string) $classroom->id)>
                                {{ $classroom->year }}{{ $classroom->name }} {{ $classroom->section }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="mb-1 block text-xs font-medium text-slate-600">Stato</label>
                    <select id="status" name="status" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
                        <option value="all" @selected($filters['status'] === 'all')>Tutti</option>
                        <option value="active" @selected($filters['status'] === 'active')>Attivi</option>
                        <option value="inactive" @selected($filters['status'] === 'inactive')>Disattivati</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Filtra</button>
                    <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Reset</a>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @if ($users->isEmpty())
                <div class="text-sm text-slate-500">Nessun utente trovato.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Utente</th>
                                <th class="px-4 py-3 text-left font-medium">Ruolo</th>
                                <th class="px-4 py-3 text-left font-medium">Classe</th>
                                <th class="px-4 py-3 text-left font-medium">Stato</th>
                                <th class="px-4 py-3 text-right font-medium">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($users as $user)
                                @php
                                    $classroomLabel = $user->classroom ? ($user->classroom->year . $user->classroom->name . ' ' . $user->classroom->section) : '-';
                                    $isActive = $user->is_active === null ? true : (bool) $user->is_active;
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-slate-900">
                                        <div class="font-medium">{{ $user->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $user->email }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">{{ $user->role }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $classroomLabel }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $isActive ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            {{ $isActive ? 'Attivo' : 'Disattivato' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">Modifica</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-sidebar>
