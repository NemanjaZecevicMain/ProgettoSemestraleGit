<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">I miei ritardi</h1>
                <p class="text-sm text-slate-500">Storico ritardi e stato firma.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4 md:items-end">
                <div>
                    <label for="firmato" class="text-xs uppercase tracking-wider text-slate-500">Firma</label>
                    <select id="firmato" name="firmato" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500">
                        <option value="all" @selected(($filters['firmato'] ?? 'all') === 'all')>Tutti</option>
                        <option value="firmati" @selected(($filters['firmato'] ?? '') === 'firmati')>Firmati</option>
                        <option value="da_firmare" @selected(($filters['firmato'] ?? '') === 'da_firmare')>Da firmare</option>
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
                    <a href="{{ route('student.delays.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:border-slate-300">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @if ($delays->count() === 0)
                <div class="text-sm text-slate-500">Nessun ritardo trovato con i filtri attuali.</div>
            @else
                <div class="hidden md:block">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium">Data</th>
                                    <th class="px-4 py-3 text-left font-medium">Minuti</th>
                                    <th class="px-4 py-3 text-left font-medium">Nota</th>
                                    <th class="px-4 py-3 text-left font-medium">Firma</th>
                                    <th class="px-4 py-3 text-right font-medium">PDF</th>
                                    <th class="px-4 py-3 text-right font-medium">Azione</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($delays as $delay)
                                    <tr>
                                        <td class="px-4 py-3 text-slate-900">{{ optional($delay->date)->format('d.m.Y') }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $delay->minutes }}</td>
                                        <td class="px-4 py-3 text-slate-700">
                                            @if ($delay->note)
                                                <div class="max-w-[220px] truncate" title="{{ $delay->note }}">{{ $delay->note }}</div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($delay->is_signed)
                                                <div class="flex flex-col gap-1">
                                                    <span class="inline-flex w-fit items-center rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800">
                                                        Firmato
                                                    </span>
                                                    @if ($delay->signed_at)
                                                        <span class="text-xs text-slate-500">{{ $delay->signed_at->format('d.m.Y H:i') }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">
                                                    Da firmare
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if ($delay->signature_file_path)
                                                <div class="flex justify-end">
                                                    <iframe
                                                        src="{{ route('student.delays.signature.download', $delay->id) }}"
                                                        class="h-24 w-32 rounded border border-slate-200 bg-white"
                                                    ></iframe>
                                                </div>
                                            @else
                                                <span class="text-xs text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="inline-flex items-center gap-3">
                                                <a href="{{ route('student.delays.show', $delay->id) }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">Apri</a>
                                                @if ($delay->signature_file_path)
                                                    <a href="{{ route('student.delays.signature.download', $delay->id) }}" class="text-xs font-medium text-slate-600 hover:text-slate-900" download>
                                                        Scarica PDF
                                                    </a>
                                                @endif
                                                @if (!$delay->is_signed)
                                                    <form method="POST" action="{{ route('student.delays.sign', $delay->id) }}" enctype="multipart/form-data" class="inline-flex items-center gap-2">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="firmato" value="{{ $filters['firmato'] ?? 'all' }}">
                                                        <input type="hidden" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                                                        <input type="hidden" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                                                        <input type="hidden" name="page" value="{{ request('page', 1) }}">
                                                        <input type="file" name="signature_file" accept="application/pdf" class="block w-44 text-xs text-slate-600 file:mr-2 file:rounded-md file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-xs file:text-slate-700" required>
                                                        <button type="submit" class="inline-flex items-center rounded-lg bg-blue-700 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-800">
                                                            Carica PDF
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-4 md:hidden">
                    @foreach ($delays as $delay)
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">{{ optional($delay->date)->format('d.m.Y') }}</div>
                                    <div class="mt-1 text-sm text-slate-600">{{ $delay->minutes }} minuti</div>
                                </div>
                                <a href="{{ route('student.delays.show', $delay->id) }}" class="text-sm font-medium text-blue-700">Apri</a>
                            </div>

                            <div class="mt-3 text-sm text-slate-600">
                                {{ $delay->note ? \Illuminate\Support\Str::limit($delay->note, 70) : 'Nessuna nota.' }}
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                @if ($delay->is_signed)
                                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800">
                                        Firmato
                                    </span>
                                    @if ($delay->signed_at)
                                        <span class="text-xs text-slate-500">{{ $delay->signed_at->format('d.m.Y H:i') }}</span>
                                    @endif
                                    @if ($delay->signature_file_path)
                                        <a href="{{ route('student.delays.signature.download', $delay->id) }}" class="text-xs font-medium text-slate-600 hover:text-slate-900" download>
                                            Scarica PDF
                                        </a>
                                    @endif
                                @else
                                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">
                                        Da firmare
                                    </span>
                                    <form method="POST" action="{{ route('student.delays.sign', $delay->id) }}" enctype="multipart/form-data" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="firmato" value="{{ $filters['firmato'] ?? 'all' }}">
                                        <input type="hidden" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                                        <input type="hidden" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                                        <input type="hidden" name="page" value="{{ request('page', 1) }}">
                                        <input type="file" name="signature_file" accept="application/pdf" class="block w-40 text-xs text-slate-600 file:mr-2 file:rounded-md file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-xs file:text-slate-700" required>
                                        <button type="submit" class="inline-flex items-center rounded-lg bg-blue-700 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-800">
                                            Carica PDF
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $delays->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-sidebar>
