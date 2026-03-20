@php
    $userValue = $targetUser ?? null;
    $selectedRole = old('role', $userValue?->role ?? 'STUDENT');
    $selectedClassroom = old('classroom_id', $userValue?->classroom_id);
    $selectedGuardian = old('guardian_id', $userValue?->guardian_id);
    $selectedTeachingClassrooms = collect(old('teaching_classroom_ids', $userValue?->taughtClassrooms?->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $isMinor = old('is_minor', $userValue?->is_minor ?? false);
    $isActive = old('is_active', $userValue?->is_active ?? true);
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label for="name" class="mb-1 block text-xs font-medium text-slate-600">Nome completo</label>
        <input id="name" name="name" type="text" value="{{ old('name', $userValue?->name) }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900" required />
        @error('name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="email" class="mb-1 block text-xs font-medium text-slate-600">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $userValue?->email) }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900" required />
        @error('email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="password" class="mb-1 block text-xs font-medium text-slate-600">Password {{ isset($targetUser) ? '(lascia vuoto per non cambiare)' : '' }}</label>
        <input id="password" name="password" type="password" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900" {{ isset($targetUser) ? '' : 'required' }} />
        @error('password') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="role" class="mb-1 block text-xs font-medium text-slate-600">Ruolo</label>
        <select id="role" name="role" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900" required>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}" @selected($selectedRole === $role->name)>{{ $role->name }}</option>
            @endforeach
        </select>
        @error('role') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="classroom_id" class="mb-1 block text-xs font-medium text-slate-600">Classe</label>
        <select id="classroom_id" name="classroom_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
            <option value="">Nessuna</option>
            @foreach ($classrooms as $classroom)
                <option value="{{ $classroom->id }}" @selected((string) $selectedClassroom === (string) $classroom->id)>
                    {{ $classroom->year }}{{ $classroom->name }} {{ $classroom->section }}
                </option>
            @endforeach
        </select>
        @error('classroom_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="guardian_id" class="mb-1 block text-xs font-medium text-slate-600">Tutore associato</label>
        <select id="guardian_id" name="guardian_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
            <option value="">Nessuno</option>
            @foreach ($guardians as $guardian)
                <option value="{{ $guardian->id }}" @selected((string) $selectedGuardian === (string) $guardian->id)>
                    {{ $guardian->name }} ({{ $guardian->email }})
                </option>
            @endforeach
        </select>
        @error('guardian_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4">
    <label for="teaching_classroom_ids" class="mb-1 block text-xs font-medium text-slate-600">Classi assegnate per insegnamento</label>
    <select id="teaching_classroom_ids" name="teaching_classroom_ids[]" multiple size="8" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
        @foreach ($classrooms as $classroom)
            <option value="{{ $classroom->id }}" @selected(in_array((int) $classroom->id, $selectedTeachingClassrooms, true))>
                {{ $classroom->year }}{{ $classroom->name }} {{ $classroom->section }}
            </option>
        @endforeach
    </select>
    <p class="mt-1 text-xs text-slate-500">Usato per TEACHER/CAPOLAB/DIREZIONE/ADMIN.</p>
    @error('teaching_classroom_ids') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
    @error('teaching_classroom_ids.*') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
</div>

<div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
    <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700">
        <input type="checkbox" name="is_minor" value="1" @checked((bool) $isMinor) />
        Utente minorenne
    </label>
    <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700">
        <input type="checkbox" name="is_active" value="1" @checked((bool) $isActive) />
        Account attivo
    </label>
</div>

<div class="mt-4">
    <label for="description" class="mb-1 block text-xs font-medium text-slate-600">Descrizione</label>
    <textarea id="description" name="description" rows="3" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">{{ old('description', $userValue?->description) }}</textarea>
    @error('description') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
</div>
