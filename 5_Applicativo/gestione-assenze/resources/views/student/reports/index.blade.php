<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Report mensili</h1>
                <p class="text-sm text-slate-500">Genera o carica il report mensile di assenze e ritardi.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Genera report</h2>
                <p class="text-sm text-slate-500">Crea il PDF dal sistema e invialo ai docenti.</p>
                <form method="POST" action="{{ route('student.reports.generate') }}" class="mt-4 flex flex-col gap-3">
                    @csrf
                    <label class="text-xs uppercase tracking-wider text-slate-500" for="generate_month">Mese</label>
                    <input id="generate_month" type="month" name="month" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500" required>
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
                        Genera e scarica
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Carica report</h2>
                <p class="text-sm text-slate-500">Carica un PDF già pronto e invialo ai docenti.</p>
                <form method="POST" action="{{ route('student.reports.upload') }}" enctype="multipart/form-data" class="mt-4 flex flex-col gap-3">
                    @csrf
                    <label class="text-xs uppercase tracking-wider text-slate-500" for="upload_month">Mese</label>
                    <input id="upload_month" type="month" name="month" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500" required>
                    <input type="file" name="report_file" accept="application/pdf" class="block text-sm text-slate-600 file:mr-2 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:text-slate-700" required>
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
                        Carica e invia
                    </button>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Report disponibili</h2>
            <p class="text-sm text-slate-500">Elenco dei report già generati o caricati.</p>

            @if ($reports->isEmpty())
                <div class="mt-4 text-sm text-slate-500">Nessun report presente.</div>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Mese</th>
                                <th class="px-4 py-3 text-left font-medium">Generato il</th>
                                <th class="px-4 py-3 text-left font-medium">Inviato il</th>
                                <th class="px-4 py-3 text-right font-medium">PDF</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($reports as $report)
                                <tr>
                                    <td class="px-4 py-3 text-slate-900">{{ $report->month }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $report->generated_at ? $report->generated_at->format('d.m.Y H:i') : '-' }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $report->sent_at ? $report->sent_at->format('d.m.Y H:i') : '-' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('student.reports.download', $report->id) }}" class="text-sm font-medium text-blue-700 hover:text-blue-900" download>
                                            Scarica
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-sidebar>
