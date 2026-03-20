<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Audit avanzato</h1>
                <p class="text-sm text-slate-500">Filtro, esportazione CSV e pulizia storica.</p>
            </div>
            <a href="{{ route('admin.audit.export', request()->query()) }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
                Esporta CSV
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.audit.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-5">
                <div>
                    <label for="action" class="mb-1 block text-xs font-medium text-slate-600">Azione</label>
                    <select id="action" name="action" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
                        <option value="">Tutte</option>
                        @foreach ($actionOptions as $action)
                            <option value="{{ $action }}" @selected($filters['action'] === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="entity_type" class="mb-1 block text-xs font-medium text-slate-600">Entita</label>
                    <select id="entity_type" name="entity_type" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
                        <option value="">Tutte</option>
                        @foreach ($entityTypeOptions as $entityType)
                            <option value="{{ $entityType }}" @selected($filters['entity_type'] === $entityType)>{{ $entityType }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="actor" class="mb-1 block text-xs font-medium text-slate-600">Operatore</label>
                    <input id="actor" name="actor" type="text" value="{{ $filters['actor'] }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
                </div>
                <div>
                    <label for="date_from" class="mb-1 block text-xs font-medium text-slate-600">Data da</label>
                    <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
                </div>
                <div>
                    <label for="date_to" class="mb-1 block text-xs font-medium text-slate-600">Data a</label>
                    <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
                </div>
                <div class="md:col-span-5 flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                        Filtra
                    </button>
                    <a href="{{ route('admin.audit.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <form method="POST" action="{{ route('admin.audit.purge') }}" onsubmit="return confirm('Confermi la pulizia del log audit?');" class="flex flex-col gap-3 md:flex-row md:items-end">
                @csrf
                @method('DELETE')
                <div>
                    <label for="before_date" class="mb-1 block text-xs font-medium text-amber-800">Elimina record precedenti a</label>
                    <input id="before_date" name="before_date" type="date" required class="rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm text-slate-900">
                    @error('before_date') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                    Esegui pulizia
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @if ($logs->isEmpty())
                <div class="text-sm text-slate-500">Nessun log trovato.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Quando</th>
                                <th class="px-4 py-3 text-left font-medium">Operatore</th>
                                <th class="px-4 py-3 text-left font-medium">Azione</th>
                                <th class="px-4 py-3 text-left font-medium">Entita</th>
                                <th class="px-4 py-3 text-left font-medium">ID</th>
                                <th class="px-4 py-3 text-left font-medium">Dettagli</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($logs as $log)
                                <tr>
                                    <td class="px-4 py-3 text-slate-900">{{ optional($log->created_at)->format('d.m.Y H:i:s') }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $log->actor?->name ?? 'Sistema' }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $log->action }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $log->entity_type }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $log->entity_id ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-700">
                                        @if (!empty($log->metadata))
                                            <pre class="max-w-[460px] overflow-auto rounded border border-slate-200 bg-slate-50 p-2 text-xs text-slate-700">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-sidebar>
