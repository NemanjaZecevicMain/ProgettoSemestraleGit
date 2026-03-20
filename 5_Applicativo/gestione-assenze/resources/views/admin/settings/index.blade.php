<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Configurazioni sistema</h1>
            <p class="text-sm text-slate-500">Parametri globali modificabili dall'amministratore.</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @csrf
                @method('PATCH')

                <div class="md:col-span-2">
                    <label for="institute_name" class="mb-1 block text-xs font-medium text-slate-600">Nome istituto</label>
                    <input id="institute_name" name="institute_name" type="text" value="{{ old('institute_name', $settings['institute_name']) }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900" />
                    @error('institute_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label for="absence_signature_expiry_days" class="mb-1 block text-xs font-medium text-slate-600">Scadenza link firma (giorni)</label>
                    <input id="absence_signature_expiry_days" name="absence_signature_expiry_days" type="number" min="1" max="60" value="{{ old('absence_signature_expiry_days', $settings['absence_signature_expiry_days']) }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900" />
                    @error('absence_signature_expiry_days') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label for="monthly_report_deadline_day" class="mb-1 block text-xs font-medium text-slate-600">Scadenza report mensile (giorno)</label>
                    <input id="monthly_report_deadline_day" name="monthly_report_deadline_day" type="number" min="1" max="31" value="{{ old('monthly_report_deadline_day', $settings['monthly_report_deadline_day']) }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900" />
                    @error('monthly_report_deadline_day') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="default_guardian_notification_email" class="mb-1 block text-xs font-medium text-slate-600">Email default notifiche tutori</label>
                    <input id="default_guardian_notification_email" name="default_guardian_notification_email" type="email" value="{{ old('default_guardian_notification_email', $settings['default_guardian_notification_email']) }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900" />
                    @error('default_guardian_notification_email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <div class="md:col-span-2">
                    <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                        Salva configurazioni
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-sidebar>
