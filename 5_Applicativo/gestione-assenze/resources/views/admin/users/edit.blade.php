<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Modifica utente</h1>
                <p class="text-sm text-slate-500">{{ $targetUser->name }} ({{ $targetUser->email }})</p>
            </div>
            <form method="POST" action="{{ route('admin.users.destroy', $targetUser->id) }}" onsubmit="return confirm('Eliminare questo utente?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg border border-rose-300 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 hover:bg-rose-100">
                    Elimina utente
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="POST" action="{{ route('admin.users.update', $targetUser->id) }}">
                @csrf
                @method('PATCH')
                @include('admin.users._form')

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                        Salva modifiche
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
                        Torna all'elenco
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-sidebar>
