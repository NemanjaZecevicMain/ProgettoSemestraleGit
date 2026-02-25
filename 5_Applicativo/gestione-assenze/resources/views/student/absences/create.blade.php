<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Segnala assenza</h1>
                <p class="text-sm text-slate-500">Compila i dati per segnalare un'assenza.</p>
            </div>
            <a href="{{ route('student.absences.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:border-slate-300">
                Torna alla lista
            </a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('student.absences.store') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-2" id="absenceForm">
                @csrf

                <div>
                    <label for="date_from" class="text-xs uppercase tracking-wider text-slate-500">Data inizio</label>
                    <input id="date_from" type="date" name="date_from" value="{{ old('date_from') }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500" required>
                    <x-input-error :messages="$errors->get('date_from')" class="mt-2" />
                </div>

                <div>
                    <label for="date_to" class="text-xs uppercase tracking-wider text-slate-500">Data fine</label>
                    <input id="date_to" type="date" name="date_to" value="{{ old('date_to') }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500" required>
                    <x-input-error :messages="$errors->get('date_to')" class="mt-2" />
                </div>

                <div>
                    <label class="text-xs uppercase tracking-wider text-slate-500">Orari primo giorno</label>
                    <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach ($slotOptions as $value => $label)
                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                <input type="checkbox" name="start_slot[]" value="{{ $value }}" @checked(in_array($value, old('start_slot', []), true)) class="rounded border-slate-300">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('start_slot')" class="mt-2" />
                </div>

                <div id="endSlotWrapper">
                    <label class="text-xs uppercase tracking-wider text-slate-500">Orari ultimo giorno</label>
                    <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach ($slotOptions as $value => $label)
                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                <input type="checkbox" name="end_slot[]" value="{{ $value }}" @checked(in_array($value, old('end_slot', []), true)) class="rounded border-slate-300">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Se l'assenza e di un solo giorno, puoi lasciare vuoto.</p>
                    <x-input-error :messages="$errors->get('end_slot')" class="mt-2" />
                </div>

                <div class="lg:col-span-2">
                    <label for="reason" class="text-xs uppercase tracking-wider text-slate-500">Motivazione</label>
                    <select id="reason" name="reason" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="">Seleziona motivazione</option>
                        @foreach ($reasonOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                </div>

                <div class="lg:col-span-2">
                    <label for="note" class="text-xs uppercase tracking-wider text-slate-500">Note (opzionale)</label>
                    <textarea id="note" name="note" rows="3" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500">{{ old('note') }}</textarea>
                    <x-input-error :messages="$errors->get('note')" class="mt-2" />
                </div>

                <div class="lg:col-span-2 flex justify-end">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
                        Invia segnalazione
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const dateFrom = document.getElementById('date_from');
        const dateTo = document.getElementById('date_to');
        const endSlotWrapper = document.getElementById('endSlotWrapper');
        const endSlotInputs = endSlotWrapper.querySelectorAll('input[name="end_slot[]"]');

        function toggleEndSlot() {
            const sameDay = dateFrom.value && dateTo.value && dateFrom.value === dateTo.value;
            endSlotWrapper.style.display = sameDay ? 'none' : 'block';
            if (sameDay) {
                endSlotInputs.forEach((input) => { input.checked = false; });
            }
        }

        dateFrom.addEventListener('change', toggleEndSlot);
        dateTo.addEventListener('change', toggleEndSlot);
        toggleEndSlot();
    </script>
</x-app-sidebar>
