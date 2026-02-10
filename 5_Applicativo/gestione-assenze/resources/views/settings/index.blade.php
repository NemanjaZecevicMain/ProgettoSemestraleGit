@php
    $user = auth()->user();
    $roleLabels = [
        'STUDENT' => 'Studente',
        'TEACHER' => 'Docente',
        'ADMIN' => 'Amministratore',
        'CAPOLAB' => 'Capolaboratorio',
        'DIREZIONE' => 'Direzione',
    ];
    $role = $roleLabels[$user->role] ?? $user->role;
    $classroomLabel = $user->classroom
        ? $user->classroom->year . $user->classroom->name . ' ' . $user->classroom->section
        : '-';
@endphp

<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Impostazioni</h1>
                <p class="text-sm text-slate-500">Gestisci le informazioni del tuo profilo.</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:text-blue-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                        <path d="M15 17h5l-1.5-1.5A2.1 2.1 0 0 1 18 14.1V11a6 6 0 1 0-12 0v3.1c0 .5-.2 1-.6 1.4L4 17h5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 17a3 3 0 0 0 6 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
                        Log out
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="h-16 w-16 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xl font-semibold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="text-lg font-semibold text-slate-900">{{ $user->name }}</div>
                            <div class="text-sm text-slate-500">{{ $role }}</div>
                        </div>
                    </div>
                    <div class="mt-4 text-sm text-slate-600">
                        {{ $user->description ?: 'Profilo studente collegato all\'istituto. Aggiorna le informazioni quando cambiano.' }}
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    @if (session('status'))
                        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div class="text-sm text-slate-500 mb-4">
                        Informazioni personali principali visibili agli amministratori.
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                            <div class="text-xs uppercase tracking-wider text-slate-500">Email</div>
                            <div class="mt-1 text-sm font-medium text-slate-900">{{ $user->email }}</div>
                        </div>
                        <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                            <div class="text-xs uppercase tracking-wider text-slate-500">Ruolo</div>
                            <div class="mt-1 text-sm font-medium text-slate-900">{{ $role }}</div>
                        </div>
                        <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                            <div class="text-xs uppercase tracking-wider text-slate-500">Classe</div>
                            <div class="mt-1 text-sm font-medium text-slate-900">{{ $classroomLabel }}</div>
                        </div>
                        <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                            <div class="text-xs uppercase tracking-wider text-slate-500">Minorenne</div>
                            <div class="mt-1 text-sm font-medium text-slate-900">{{ $user->is_minor ? 'Si' : 'No' }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('settings.description.update') }}" class="mt-6">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label for="description" class="text-xs uppercase tracking-wider text-slate-500">Descrizione</label>
                            <textarea id="description" name="description" rows="4" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500" placeholder="Scrivi una breve descrizione...">{{ old('description', $user->description) }}</textarea>
                            @error('description')
                                <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mt-3 flex justify-end">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-800 hover:bg-blue-100">
                                Modifica descrizione
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-sidebar>
