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
                <h1 class="text-2xl font-semibold text-slate-900">Certificati</h1>
                <p class="text-sm text-slate-500">Certificati medici associati alle tue assenze.</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @if ($absences->count() === 0)
                <div class="text-sm text-slate-500">Nessuna assenza trovata.</div>
            @else
                <div class="hidden md:block">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium">Periodo</th>
                                    <th class="px-4 py-3 text-left font-medium">Motivo</th>
                                    <th class="px-4 py-3 text-left font-medium">Stato</th>
                                    <th class="px-4 py-3 text-left font-medium">Certificati</th>
                                    <th class="px-4 py-3 text-right font-medium">Dettaglio</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($absences as $absence)
                                    @php
                                        $statusLabel = $statusOptions[$absence->status] ?? $absence->status;
                                        $statusClass = $statusStyles[$absence->status] ?? 'border-slate-200 bg-slate-100 text-slate-700';
                                        $certificatesBySlot = $absence->certificates->keyBy('slot');
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
                                            <div class="flex flex-wrap items-center gap-2">
                                                @for ($slot = 1; $slot <= 3; $slot++)
                                                    @php
                                                        $certificate = $certificatesBySlot->get($slot);
                                                    @endphp
                                                    @if ($certificate)
                                                        <a href="{{ route('student.absences.certificates.download', [$absence->id, $slot]) }}" class="inline-flex items-center rounded border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] font-medium text-slate-700 hover:border-slate-300" download>
                                                            PDF {{ $slot }}
                                                        </a>
                                                    @else
                                                        <span class="inline-flex items-center rounded border border-slate-100 bg-slate-50 px-2 py-1 text-[11px] text-slate-400">
                                                            - {{ $slot }}
                                                        </span>
                                                    @endif
                                                @endfor
                                            </div>
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
                            $certificatesBySlot = $absence->certificates->keyBy('slot');
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
                            </div>
                            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                                @for ($slot = 1; $slot <= 3; $slot++)
                                    @php
                                        $certificate = $certificatesBySlot->get($slot);
                                    @endphp
                                    @if ($certificate)
                                        <a href="{{ route('student.absences.certificates.download', [$absence->id, $slot]) }}" class="inline-flex items-center rounded border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] font-medium text-slate-700" download>
                                            PDF {{ $slot }}
                                        </a>
                                    @else
                                        <span class="inline-flex items-center rounded border border-slate-100 bg-slate-50 px-2 py-1 text-[11px] text-slate-400">
                                            - {{ $slot }}
                                        </span>
                                    @endif
                                @endfor
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
