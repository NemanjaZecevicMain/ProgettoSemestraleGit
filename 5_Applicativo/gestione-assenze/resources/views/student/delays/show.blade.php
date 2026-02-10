<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Dettaglio ritardo</h1>
                <p class="text-sm text-slate-500">Riepilogo completo del ritardo selezionato.</p>
            </div>
            <a href="{{ route('student.delays.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:border-slate-300">
                Torna alla lista
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center gap-3">
                @if ($delay->is_signed)
                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800">
                        Firmato
                    </span>
                    @if ($delay->signed_at)
                        <span class="text-xs text-slate-500">Firmato il {{ $delay->signed_at->format('d.m.Y H:i') }}</span>
                    @endif
                @else
                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">
                        Da firmare
                    </span>
                @endif
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-wider text-slate-500">Data</div>
                    <div class="mt-2 text-sm font-medium text-slate-900">{{ optional($delay->date)->format('d.m.Y') }}</div>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-wider text-slate-500">Minuti</div>
                    <div class="mt-2 text-sm font-medium text-slate-900">{{ $delay->minutes }}</div>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-wider text-slate-500">Inserito da</div>
                    <div class="mt-2 text-sm font-medium text-slate-900">-</div>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-wider text-slate-500">Studente firmatario</div>
                    <div class="mt-2 text-sm font-medium text-slate-900">{{ $delay->signed_by_user_id ?: '-' }}</div>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-slate-100 bg-slate-50 p-4">
                <div class="text-xs uppercase tracking-wider text-slate-500">Nota</div>
                <div class="mt-2 text-sm text-slate-900">
                    {{ $delay->note ?: 'Nessuna nota disponibile.' }}
                </div>
            </div>

            @if (!$delay->is_signed)
                <div class="mt-6">
                    <form method="POST" action="{{ route('student.delays.sign', $delay->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
                            Firma
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-sidebar>
