<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Ruoli e permessi</h1>
            <p class="text-sm text-slate-500">Matrice di autorizzazione per ogni ruolo.</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4">
            @foreach ($roles as $role)
                @php
                    $selected = $role->permissions->pluck('id')->all();
                @endphp
                <form method="POST" action="{{ route('admin.roles-permissions.update', $role->id) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3 flex items-center justify-between">
                        <div class="text-base font-semibold text-slate-900">{{ $role->name }}</div>
                        <button type="submit" class="rounded-lg bg-blue-700 px-3 py-2 text-sm font-medium text-white hover:bg-blue-800">
                            Salva permessi
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                        @foreach ($permissions as $permission)
                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700">
                                <input
                                    type="checkbox"
                                    name="permission_ids[]"
                                    value="{{ $permission->id }}"
                                    @checked(in_array($permission->id, old('permission_ids', $selected), true))
                                />
                                <span>{{ $permission->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </form>
            @endforeach
        </div>
    </div>
</x-app-sidebar>
