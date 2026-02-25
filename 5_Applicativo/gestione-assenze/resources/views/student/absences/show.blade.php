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

    $statusLabel = $statusLabels[$absence->status] ?? $absence->status;
    $statusClass = $statusStyles[$absence->status] ?? 'border-slate-200 bg-slate-100 text-slate-700';

    $certificatesBySlot = $absence->certificates->keyBy('slot');
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

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>
                @if ($absence->is_signed)
                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800">
                        Firmato
                    </span>
                    @if ($absence->signed_at)
                        <span class="text-xs text-slate-500">Firmato il {{ $absence->signed_at->format('d.m.Y H:i') }}</span>
                    @endif
                @elseif ($absence->status === 'WAITING_SIGNATURE')
                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">
                        Da firmare
                    </span>
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
                    <div class="text-xs uppercase tracking-wider text-slate-500">Orari</div>
                    <div class="mt-2 text-sm font-medium text-slate-900">
                        @php
                            $startSlots = is_array($absence->time_from) ? $absence->time_from : [];
                            $endSlots = is_array($absence->time_to) ? $absence->time_to : [];
                        @endphp
                        @if ($startSlots && $endSlots)
                            {{ implode(', ', $startSlots) }} / {{ implode(', ', $endSlots) }}
                        @elseif ($startSlots)
                            {{ implode(', ', $startSlots) }}
                        @else
                            -
                        @endif
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
            </div>

            <div class="mt-6 rounded-xl border border-slate-100 bg-slate-50 p-4">
                <div class="text-xs uppercase tracking-wider text-slate-500">Note</div>
                <div class="mt-2 text-sm text-slate-900">
                    {{ $absence->note ?: 'Nessuna nota disponibile.' }}
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Firma</h2>
                        <p class="text-sm text-slate-500">Carica o sostituisci il PDF della firma.</p>
                    </div>
                    @if (!$absence->is_signed && $absence->status !== 'WAITING_SIGNATURE')
                        <span class="text-xs text-amber-700">Assenza non firmabile.</span>
                    @endif
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2 lg:items-start">
                    <form method="POST" action="{{ route('student.absences.sign', $absence->id) }}" enctype="multipart/form-data" class="rounded-xl border border-slate-200 bg-white p-4">
                        @csrf
                        @method('PATCH')
                        <div class="text-xs uppercase tracking-wider text-slate-500">PDF firma</div>
                        <div class="mt-3 flex flex-col gap-3">
                            <input type="file" name="signature_file" accept="application/pdf" class="block text-sm text-slate-600 file:mr-2 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:text-slate-700" required>
                            <button type="submit" class="inline-flex items-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
                                {{ $absence->signature_file_path ? 'Sostituisci PDF' : 'Carica PDF' }}
                            </button>
                        </div>
                    </form>
                    <div class="rounded-xl border border-slate-200 bg-white p-4">
                        <div class="text-xs uppercase tracking-wider text-slate-500">Anteprima</div>
                        @if ($absence->signature_file_path)
                            <iframe
                                src="{{ route('student.absences.signature.download', $absence->id) }}"
                                class="mt-3 h-64 w-full rounded border border-slate-200 bg-white"
                            ></iframe>
                            <div class="mt-3">
                                <a href="{{ route('student.absences.signature.download', $absence->id) }}" class="text-sm font-medium text-blue-700 hover:text-blue-900" download>
                                    Scarica PDF
                                </a>
                                <span class="ml-2 text-xs text-slate-500">Se l'anteprima non si vede, usa il download.</span>
                            </div>
                        @else
                            <div class="mt-3 flex h-64 items-center justify-center rounded-lg border border-dashed border-slate-200 bg-slate-50 text-sm text-slate-500">
                                Nessun PDF caricato
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-lg font-semibold text-slate-900">Certificati medici</h2>
                <p class="text-sm text-slate-500">Puoi caricare fino a 3 certificati PDF per questa assenza.</p>

                <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
                    @for ($slot = 1; $slot <= 3; $slot++)
                        @php
                            $certificate = $certificatesBySlot->get($slot);
                        @endphp
                        <div class="rounded-xl border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between">
                                <div class="text-xs uppercase tracking-wider text-slate-500">Certificato {{ $slot }}</div>
                                @if ($certificate)
                                    <a href="{{ route('student.absences.certificates.download', [$absence->id, $slot]) }}" class="text-xs font-medium text-blue-700 hover:text-blue-900" download>
                                        Scarica PDF
                                    </a>
                                @endif
                            </div>

                            <div class="mt-3">
                                @if ($certificate)
                                    <iframe
                                        src="{{ route('student.absences.certificates.download', [$absence->id, $slot]) }}"
                                        class="h-40 w-full rounded border border-slate-200 bg-white"
                                    ></iframe>
                                @else
                                    <div class="flex h-40 items-center justify-center rounded-lg border border-dashed border-slate-200 bg-slate-50 text-sm text-slate-500">
                                        Nessun PDF caricato
                                    </div>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('student.absences.certificates.upload', [$absence->id, $slot]) }}" enctype="multipart/form-data" class="mt-3 flex flex-col gap-2">
                                @csrf
                                <input type="file" name="certificate_file" accept="application/pdf" class="block text-sm text-slate-600 file:mr-2 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:text-slate-700" required>
                                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-800">
                                    {{ $certificate ? 'Sostituisci PDF' : 'Carica PDF' }}
                                </button>
                            </form>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
</x-app-sidebar>
