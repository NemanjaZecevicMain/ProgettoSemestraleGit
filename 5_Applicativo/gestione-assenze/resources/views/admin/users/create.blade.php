<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Nuovo utente</h1>
            <p class="text-sm text-slate-500">Creazione account con ruolo e assegnazioni.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                @include('admin.users._form')

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                        Crea utente
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
                        Annulla
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-sidebar>
