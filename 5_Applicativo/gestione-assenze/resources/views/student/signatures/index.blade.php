@php
    $statusStyles = [
        'PENDING' => 'border-slate-200 bg-slate-100 text-slate-700',
        'WAITING_CERT' => 'border-amber-200 bg-amber-100 text-amber-800',
        'WAITING_SIGNATURE' => 'border-amber-200 bg-amber-100 text-amber-800',
        'JUSTIFIED' => 'border-emerald-200 bg-emerald-100 text-emerald-800',
        'UNJUSTIFIED' => 'border-rose-200 bg-rose-100 text-rose-800',
    ];
@endphp

<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Stato firme</h1>
                <p class="text-sm text-slate-500">Riepilogo delle firme per ritardi e assenze.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase tracking-wider text-slate-500">Ritardi da firmare</div>
                <div class="mt-2 text-2xl font-semibold text-amber-700">{{ $summary['delays_unsigned'] }}</div>
                <div class="text-xs text-slate-400">Totale</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase tracking-wider text-slate-500">Ritardi firmati</div>
                <div class="mt-2 text-2xl font-semibold text-emerald-700">{{ $summary['delays_signed'] }}</div>
                <div class="text-xs text-slate-400">Totale</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase tracking-wider text-slate-500">Assenze da firmare</div>
                <div class="mt-2 text-2xl font-semibold text-amber-700">{{ $summary['absences_unsigned'] }}</div>
                <div class="text-xs text-slate-400">Totale</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase tracking-wider text-slate-500">Assenze firmate</div>
                <div class="mt-2 text-2xl font-semibold text-emerald-700">{{ $summary['absences_signed'] }}</div>
                <div class="text-xs text-slate-400">Totale</div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Ritardi</h2>
                    <p class="text-sm text-slate-500">Ultimi 10 ritardi con stato firma.</p>
                </div>
                <a href="{{ route('student.delays.index') }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:border-slate-300">
                    Vai ai ritardi
                </a>
            </div>

            @if ($delays->isEmpty())
                <div class="mt-4 text-sm text-slate-500">Nessun ritardo disponibile.</div>
            @else
                <div class="mt-4 hidden md:block">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium">Data</th>
                                    <th class="px-4 py-3 text-left font-medium">Minuti</th>
                                    <th class="px-4 py-3 text-left font-medium">Firma</th>
                                    <th class="px-4 py-3 text-left font-medium">Data firma</th>
                                    <th class="px-4 py-3 text-right font-medium">PDF</th>
                                    <th class="px-4 py-3 text-right font-medium">Dettaglio</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($delays as $delay)
                                    <tr>
                                        <td class="px-4 py-3 text-slate-900">{{ optional($delay->date)->format('d.m.Y') }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $delay->minutes }}</td>
                                        <td class="px-4 py-3">
                                            @if ($delay->is_signed)
                                                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800">
                                                    Firmato
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">
                                                    Da firmare
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-700">
                                            {{ $delay->signed_at ? $delay->signed_at->format('d.m.Y H:i') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if ($delay->signature_file_path)
                                                <div class="flex justify-end">
                                                    <iframe
                                                        src="{{ route('student.delays.signature.download', $delay->id) }}"
                                                        class="h-20 w-28 rounded border border-slate-200 bg-white"
                                                    ></iframe>
                                                </div>
                                            @else
                                                <span class="text-xs text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('student.delays.show', $delay->id) }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">Apri</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 space-y-4 md:hidden">
                    @foreach ($delays as $delay)
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">{{ optional($delay->date)->format('d.m.Y') }}</div>
                                    <div class="mt-1 text-sm text-slate-600">{{ $delay->minutes }} minuti</div>
                                </div>
                                <a href="{{ route('student.delays.show', $delay->id) }}" class="text-sm font-medium text-blue-700">Apri</a>
                            </div>
                            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                                @if ($delay->is_signed)
                                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-1 font-medium text-emerald-800">
                                        Firmato
                                    </span>
                                    @if ($delay->signed_at)
                                        <span class="text-slate-500">{{ $delay->signed_at->format('d.m.Y H:i') }}</span>
                                    @endif
                                    @if ($delay->signature_file_path)
                                        <iframe
                                            src="{{ route('student.delays.signature.download', $delay->id) }}"
                                            class="h-20 w-28 rounded border border-slate-200 bg-white"
                                        ></iframe>
                                    @endif
                                @else
                                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2.5 py-1 font-medium text-amber-800">
                                        Da firmare
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Assenze</h2>
                    <p class="text-sm text-slate-500">Ultime 10 assenze con stato firma.</p>
                </div>
                <a href="{{ route('student.absences.index') }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:border-slate-300">
                    Vai alle assenze
                </a>
            </div>

            @if ($absences->isEmpty())
                <div class="mt-4 text-sm text-slate-500">Nessuna assenza disponibile.</div>
            @else
                <div class="mt-4 hidden md:block">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium">Periodo</th>
                                    <th class="px-4 py-3 text-left font-medium">Motivo</th>
                                    <th class="px-4 py-3 text-left font-medium">Stato</th>
                                    <th class="px-4 py-3 text-left font-medium">Firma</th>
                                    <th class="px-4 py-3 text-right font-medium">PDF</th>
                                    <th class="px-4 py-3 text-right font-medium">Dettaglio</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($absences as $absence)
                                    @php
                                        $statusLabel = $statusOptions[$absence->status] ?? $absence->status;
                                        $statusClass = $statusStyles[$absence->status] ?? 'border-slate-200 bg-slate-100 text-slate-700';
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
                                            @if ($absence->is_signed)
                                                <div class="flex flex-col gap-1">
                                                    <span class="inline-flex w-fit items-center rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800">
                                                        Firmato
                                                    </span>
                                                    @if ($absence->signed_at)
                                                        <span class="text-xs text-slate-500">{{ $absence->signed_at->format('d.m.Y H:i') }}</span>
                                                    @endif
                                                </div>
                                            @elseif ($absence->status === 'WAITING_SIGNATURE')
                                                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">
                                                    Da firmare
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if ($absence->signature_file_path)
                                                <div class="flex justify-end">
                                                    <iframe
                                                        src="{{ route('student.absences.signature.download', $absence->id) }}"
                                                        class="h-20 w-28 rounded border border-slate-200 bg-white"
                                                    ></iframe>
                                                </div>
                                            @else
                                                <span class="text-xs text-slate-400">-</span>
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

                <div class="mt-4 space-y-4 md:hidden">
                    @foreach ($absences as $absence)
                        @php
                            $statusLabel = $statusOptions[$absence->status] ?? $absence->status;
                            $statusClass = $statusStyles[$absence->status] ?? 'border-slate-200 bg-slate-100 text-slate-700';
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
                            <div class="mt-3">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                                @if ($absence->is_signed)
                                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800">
                                        Firmato
                                    </span>
                                    @if ($absence->signed_at)
                                        <span class="text-slate-500">{{ $absence->signed_at->format('d.m.Y H:i') }}</span>
                                    @endif
                                    @if ($absence->signature_file_path)
                                        <iframe
                                            src="{{ route('student.absences.signature.download', $absence->id) }}"
                                            class="mt-2 h-20 w-28 rounded border border-slate-200 bg-white"
                                        ></iframe>
                                    @endif
                                @elseif ($absence->status === 'WAITING_SIGNATURE')
                                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">
                                        Da firmare
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-sidebar>
