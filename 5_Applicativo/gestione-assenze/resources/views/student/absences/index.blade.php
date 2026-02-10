@php
    $statusStyles = [
        'PENDING' => 'border-slate-200 bg-slate-100 text-slate-700',
        'WAITING_CERT' => 'border-amber-200 bg-amber-100 text-amber-800',
        'WAITING_SIGNATURE' => 'border-amber-200 bg-amber-100 text-amber-800',
        'JUSTIFIED' => 'border-emerald-200 bg-emerald-100 text-emerald-800',
        'UNJUSTIFIED' => 'border-rose-200 bg-rose-100 text-rose-800',
    ];

    $approvalStyles = [
        'pending' => 'border-slate-200 bg-slate-100 text-slate-700',
        'approved' => 'border-emerald-200 bg-emerald-100 text-emerald-800',
        'rejected' => 'border-rose-200 bg-rose-100 text-rose-800',
    ];
@endphp

<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Le mie assenze</h1>
                <p class="text-sm text-slate-500">Consulta lo storico delle tue assenze.</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4 md:items-end">
                <div>
                    <label for="status" class="text-xs uppercase tracking-wider text-slate-500">Stato</label>
                    <select id="status" name="status" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500">
                        <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Tutti</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date_from" class="text-xs uppercase tracking-wider text-slate-500">Data da</label>
                    <input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label for="date_to" class="text-xs uppercase tracking-wider text-slate-500">Data a</label>
                    <input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
                        Applica filtri
                    </button>
                    <a href="{{ route('student.absences.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:border-slate-300">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @if ($absences->count() === 0)
                <div class="text-sm text-slate-500">Nessuna assenza trovata con i filtri attuali.</div>
            @else
                <div class="hidden md:block">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium">Periodo</th>
                                    <th class="px-4 py-3 text-left font-medium">Motivo</th>
                                    <th class="px-4 py-3 text-left font-medium">Stato</th>
                                    <th class="px-4 py-3 text-left font-medium">Approvazione</th>
                                    <th class="px-4 py-3 text-left font-medium">Ore</th>
                                    <th class="px-4 py-3 text-left font-medium">Note</th>
                                    <th class="px-4 py-3 text-right font-medium">Dettaglio</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($absences as $absence)
                                    @php
                                        $statusLabel = $statusOptions[$absence->status] ?? $absence->status;
                                        $statusClass = $statusStyles[$absence->status] ?? 'border-slate-200 bg-slate-100 text-slate-700';
                                        if ($absence->is_approved === null) {
                                            $approvalLabel = 'In attesa';
                                            $approvalClass = $approvalStyles['pending'];
                                        } elseif ($absence->is_approved) {
                                            $approvalLabel = 'Approvata';
                                            $approvalClass = $approvalStyles['approved'];
                                        } else {
                                            $approvalLabel = 'Respinta';
                                            $approvalClass = $approvalStyles['rejected'];
                                        }
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 text-slate-900">
                                            {{ optional($absence->date_from)->format('d.m.Y') }} &rarr; {{ optional($absence->date_to)->format('d.m.Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-700">{{ $absence->reason }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-col gap-1">
                                                <span class="inline-flex w-fit items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $approvalClass }}">
                                                    {{ $approvalLabel }}
                                                </span>
                                                @if ($absence->approved_at)
                                                    <span class="text-xs text-slate-500">{{ $absence->approved_at->format('d.m.Y H:i') }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-700">
                                            {{ $absence->hours_assigned ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-700">
                                            @if ($absence->note)
                                                <div class="max-w-[180px] truncate" title="{{ $absence->note }}">{{ $absence->note }}</div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('student.absences.show', $absence->id) }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">Apri</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-4 md:hidden">
                    @foreach ($absences as $absence)
                        @php
                            $statusLabel = $statusOptions[$absence->status] ?? $absence->status;
                            $statusClass = $statusStyles[$absence->status] ?? 'border-slate-200 bg-slate-100 text-slate-700';
                            if ($absence->is_approved === null) {
                                $approvalLabel = 'In attesa';
                                $approvalClass = $approvalStyles['pending'];
                            } elseif ($absence->is_approved) {
                                $approvalLabel = 'Approvata';
                                $approvalClass = $approvalStyles['approved'];
                            } else {
                                $approvalLabel = 'Respinta';
                                $approvalClass = $approvalStyles['rejected'];
                            }
                        @endphp
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">
                                        {{ optional($absence->date_from)->format('d.m.Y') }} &rarr; {{ optional($absence->date_to)->format('d.m.Y') }}
                                    </div>
                                    <div class="mt-1 text-sm text-slate-600">{{ $absence->reason }}</div>
                                </div>
                                <a href="{{ route('student.absences.show', $absence->id) }}" class="text-sm font-medium text-blue-700">Apri</a>
                            </div>
                            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 font-medium {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 font-medium {{ $approvalClass }}">
                                    {{ $approvalLabel }}
                                </span>
                                @if ($absence->approved_at)
                                    <span class="text-slate-500">{{ $absence->approved_at->format('d.m.Y H:i') }}</span>
                                @endif
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-3 text-sm text-slate-600">
                                <div>
                                    <div class="text-xs uppercase tracking-wider text-slate-400">Ore</div>
                                    <div class="mt-1 text-slate-900">{{ $absence->hours_assigned ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs uppercase tracking-wider text-slate-400">Note</div>
                                    <div class="mt-1 text-slate-900">
                                        {{ $absence->note ? \Illuminate\Support\Str::limit($absence->note, 40) : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $absences->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-sidebar>
