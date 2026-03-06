<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Approvazione assenze</h1>
            <p class="text-sm text-slate-500">
                @if ($requiredRole === 'CAPOLAB')
                    Storico richieste con inizio e fine nello stesso giorno.
                @else
                    Storico richieste su piu giorni.
                @endif
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('approvals.absences.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-6">
                <div class="md:col-span-2">
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
                <div>
                    <label for="approval" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Esito</label>
                    <select id="approval" name="approval" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
                        <option value="all" @selected($filters['approval'] === 'all')>Tutte</option>
                        <option value="pending" @selected($filters['approval'] === 'pending')>In valutazione</option>
                        <option value="approved" @selected($filters['approval'] === 'approved')>Approvate</option>
                        <option value="rejected" @selected($filters['approval'] === 'rejected')>Rifiutate</option>
                    </select>
                </div>
                <div>
                    <label for="status" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Stato</label>
                    <select id="status" name="status" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
                        <option value="all" @selected($filters['status'] === 'all')>Tutti</option>
                        @foreach ($statusOptions as $statusKey => $statusLabel)
                            <option value="{{ $statusKey }}" @selected($filters['status'] === $statusKey)>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="reason" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Motivazione</label>
                    <select id="reason" name="reason" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900">
                        <option value="all" @selected($filters['reason'] === 'all')>Tutte</option>
                        @foreach ($reasonOptions as $reasonKey => $reasonLabel)
                            <option value="{{ $reasonKey }}" @selected($filters['reason'] === $reasonKey)>{{ $reasonLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4 flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                        Filtra
                    </button>
                    <a href="{{ route('approvals.absences.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:border-slate-300">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @if ($absences->isEmpty())
                <div class="text-sm text-slate-500">Nessuna richiesta trovata con questi filtri.</div>
            @else
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Studente</th>
                                <th class="px-4 py-3 text-left font-medium">Periodo</th>
                                <th class="px-4 py-3 text-left font-medium">Motivazione</th>
                                <th class="px-4 py-3 text-left font-medium">Stato</th>
                                <th class="px-4 py-3 text-left font-medium">Esito</th>
                                <th class="px-4 py-3 text-left font-medium">Decisione</th>
                                <th class="px-4 py-3 text-left font-medium">Note</th>
                                <th class="px-4 py-3 text-left font-medium">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($absences as $absence)
                                @php
                                    $approvalLabel = $absence->is_approved === null
                                        ? 'In valutazione'
                                        : ($absence->is_approved ? 'Approvata' : 'Rifiutata');
                                    $decisionLabel = $absence->approvedBy
                                        ? $absence->approvedBy->name . ' - ' . optional($absence->approved_at)->format('d.m.Y H:i')
                                        : '-';
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-slate-900">{{ $absence->student?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-700">
                                        {{ optional($absence->date_from)->format('d.m.Y') }} - {{ optional($absence->date_to)->format('d.m.Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">{{ $absence->reason }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $statusOptions[$absence->status] ?? $absence->status }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $approvalLabel }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $decisionLabel }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $absence->note ?: '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($absence->is_approved === null && $absence->status === 'PENDING')
                                            <div class="flex flex-wrap gap-2">
                                                <form method="POST" action="{{ route('approvals.absences.approve', $absence->id) }}">
                                                    @csrf
                                                    <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                                        Approva
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('approvals.absences.reject', $absence->id) }}">
                                                    @csrf
                                                    <button type="submit" class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
                                                        Rifiuta
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-500">Nessuna azione</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-1 gap-3 lg:hidden">
                    @foreach ($absences as $absence)
                        @php
                            $approvalLabel = $absence->is_approved === null
                                ? 'In valutazione'
                                : ($absence->is_approved ? 'Approvata' : 'Rifiutata');
                        @endphp
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-sm font-semibold text-slate-900">{{ $absence->student?->name ?? '-' }}</div>
                            <div class="mt-1 text-xs text-slate-600">
                                {{ optional($absence->date_from)->format('d.m.Y') }} - {{ optional($absence->date_to)->format('d.m.Y') }}
                            </div>
                            <div class="mt-1 text-xs text-slate-600">{{ $absence->reason }}</div>
                            <div class="mt-1 text-xs text-slate-600">Stato: {{ $statusOptions[$absence->status] ?? $absence->status }}</div>
                            <div class="mt-1 text-xs text-slate-600">Esito: {{ $approvalLabel }}</div>
                            <div class="mt-3 flex gap-2">
                                @if ($absence->is_approved === null && $absence->status === 'PENDING')
                                    <form method="POST" action="{{ route('approvals.absences.approve', $absence->id) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white">
                                            Approva
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('approvals.absences.reject', $absence->id) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white">
                                            Rifiuta
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-500">Nessuna azione disponibile</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $absences->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-sidebar>
