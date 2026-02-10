@php
    $statusStyles = [
        'PENDING' => 'border-slate-200 bg-slate-100 text-slate-700',
        'WAITING_CERT' => 'border-amber-200 bg-amber-100 text-amber-800',
        'WAITING_SIGNATURE' => 'border-amber-200 bg-amber-100 text-amber-800',
        'JUSTIFIED' => 'border-emerald-200 bg-emerald-100 text-emerald-800',
        'UNJUSTIFIED' => 'border-rose-200 bg-rose-100 text-rose-800',
    ];

    $statusLabels = [
        'PENDING' => 'In attesa',
        'WAITING_CERT' => 'Attesa certificato',
        'WAITING_SIGNATURE' => 'Attesa firma',
        'JUSTIFIED' => 'Giustificata',
        'UNJUSTIFIED' => 'Non giustificata',
    ];

    if ($absence->is_approved === null) {
        $approvalLabel = 'In attesa';
        $approvalClass = 'border-slate-200 bg-slate-100 text-slate-700';
    } elseif ($absence->is_approved) {
        $approvalLabel = 'Approvata';
        $approvalClass = 'border-emerald-200 bg-emerald-100 text-emerald-800';
    } else {
        $approvalLabel = 'Respinta';
        $approvalClass = 'border-rose-200 bg-rose-100 text-rose-800';
    }

    $statusLabel = $statusLabels[$absence->status] ?? $absence->status;
    $statusClass = $statusStyles[$absence->status] ?? 'border-slate-200 bg-slate-100 text-slate-700';
@endphp

<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Dettaglio assenza</h1>
                <p class="text-sm text-slate-500">Riepilogo completo della richiesta.</p>
            </div>
            <a href="{{ route('student.absences.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:border-slate-300">
                Torna alla lista
            </a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $approvalClass }}">
                    {{ $approvalLabel }}
                </span>
                @if ($absence->approved_at)
                    <span class="text-xs text-slate-500">Approvata il {{ $absence->approved_at->format('d.m.Y H:i') }}</span>
                @endif
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-wider text-slate-500">Periodo</div>
                    <div class="mt-2 text-sm font-medium text-slate-900">
                        {{ optional($absence->date_from)->format('d.m.Y') }} &rarr; {{ optional($absence->date_to)->format('d.m.Y') }}
                    </div>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-wider text-slate-500">Motivo</div>
                    <div class="mt-2 text-sm font-medium text-slate-900">{{ $absence->reason }}</div>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-wider text-slate-500">Ore assegnate</div>
                    <div class="mt-2 text-sm font-medium text-slate-900">{{ $absence->hours_assigned ?? '-' }}</div>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-wider text-slate-500">Stato approvazione</div>
                    <div class="mt-2 text-sm font-medium text-slate-900">{{ $approvalLabel }}</div>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-slate-100 bg-slate-50 p-4">
                <div class="text-xs uppercase tracking-wider text-slate-500">Note</div>
                <div class="mt-2 text-sm text-slate-900">
                    {{ $absence->note ?: 'Nessuna nota disponibile.' }}
                </div>
            </div>
        </div>
    </div>
</x-app-sidebar>
