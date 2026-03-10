<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Storico operazioni</h1>
            <p class="text-sm text-slate-500">Registro audit delle azioni principali su assenze e firme.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('audit.logs.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-6">
                <div>
                    <label for="action" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Azione</label>
                    <select id="action" name="action" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
                        <option value="">Tutte</option>
                        @foreach ($actionOptions as $action)
                            <option value="{{ $action }}" @selected($filters['action'] === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="entity_type" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Entita</label>
                    <select id="entity_type" name="entity_type" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
                        <option value="">Tutte</option>
                        @foreach ($entityTypeOptions as $entityType)
                            <option value="{{ $entityType }}" @selected($filters['entity_type'] === $entityType)>{{ $entityType }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="actor" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Operatore</label>
                    <input id="actor" name="actor" type="text" value="{{ $filters['actor'] }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900" placeholder="Nome o email">
                </div>
                <div>
                    <label for="student" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Studente</label>
                    <input id="student" name="student" type="text" value="{{ $filters['student'] }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900" placeholder="Nome studente">
                </div>
                <div>
                    <label for="date_from" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Data da</label>
                    <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
                </div>
                <div>
                    <label for="date_to" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Data a</label>
                    <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
                </div>
                <div class="md:col-span-6 flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                        Filtra
                    </button>
                    <a href="{{ route('audit.logs.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:border-slate-300">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @if ($logs->isEmpty())
                <div class="text-sm text-slate-500">Nessuna operazione trovata con i filtri selezionati.</div>
            @else
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Quando</th>
                                <th class="px-4 py-3 text-left font-medium">Operatore</th>
                                <th class="px-4 py-3 text-left font-medium">Azione</th>
                                <th class="px-4 py-3 text-left font-medium">Entita</th>
                                <th class="px-4 py-3 text-left font-medium">ID</th>
                                <th class="px-4 py-3 text-left font-medium">Studente</th>
                                <th class="px-4 py-3 text-left font-medium">Dettagli</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($logs as $log)
                                @php
                                    $metadata = is_array($log->metadata) ? $log->metadata : [];
                                    $studentLabel = $log->absence?->student?->name ?? '-';
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-slate-900">{{ optional($log->created_at)->format('d.m.Y H:i:s') }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $log->actor?->name ?? 'Sistema' }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $log->action }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $log->entity_type }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $log->entity_id ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $studentLabel }}</td>
                                    <td class="px-4 py-3 text-slate-700">
                                        @if (!empty($metadata))
                                            <pre class="max-w-[460px] overflow-auto rounded border border-slate-200 bg-slate-50 p-2 text-xs text-slate-700">{{ json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-1 gap-3 lg:hidden">
                    @foreach ($logs as $log)
                        @php
                            $metadata = is_array($log->metadata) ? $log->metadata : [];
                        @endphp
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-sm font-semibold text-slate-900">{{ $log->action }}</div>
                            <div class="mt-1 text-xs text-slate-600">{{ optional($log->created_at)->format('d.m.Y H:i:s') }}</div>
                            <div class="mt-1 text-xs text-slate-600">Operatore: {{ $log->actor?->name ?? 'Sistema' }}</div>
                            <div class="mt-1 text-xs text-slate-600">Entita: {{ $log->entity_type }} #{{ $log->entity_id ?? '-' }}</div>
                            @if (!empty($metadata))
                                <pre class="mt-2 overflow-auto rounded border border-slate-200 bg-slate-50 p-2 text-xs text-slate-700">{{ json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-sidebar>
