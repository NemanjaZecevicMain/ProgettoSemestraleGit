<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Le mie classi</h1>
            <p class="text-sm text-slate-500">Classi assegnate al docente.</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @php
            $user = auth()->user();
            $canImportClassrooms = in_array($user?->role, ['CAPOLAB', 'ADMIN'], true);
            $importErrors = session('import_errors', []);
        @endphp

        @if ($canImportClassrooms)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-slate-900">Import classi da file</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Formati supportati: <code>year,name,section</code> oppure template studenti con colonna <code>Sezione</code> (es. <code>I4AA</code>).
                    Formati file supportati: CSV/TXT/XLSX.
                </p>
                <form method="POST" action="{{ route('teacher.classes.import') }}" enctype="multipart/form-data" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                    @csrf
                    <input type="file" name="csv_file" accept=".csv,.txt,.xlsx" class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 sm:w-auto" required>
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
                        Importa file
                    </button>
                </form>
                @error('csv_file')
                    <div class="mt-3 text-sm text-red-600">{{ $message }}</div>
                @enderror
                @if (count($importErrors) > 0)
                    <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                        <div class="font-medium">Righe ignorate:</div>
                        <ul class="mt-1 list-disc pl-5">
                            @foreach (array_slice($importErrors, 0, 8) as $lineError)
                                <li>{{ $lineError }}</li>
                            @endforeach
                        </ul>
                        @if (count($importErrors) > 8)
                            <div class="mt-1">Altri {{ count($importErrors) - 8 }} errori non mostrati.</div>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @if ($classrooms->isEmpty())
                <div class="text-sm text-slate-500">Nessuna classe assegnata.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Classe</th>
                                <th class="px-4 py-3 text-left font-medium">Studenti</th>
                                <th class="px-4 py-3 text-right font-medium">Apri</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($classrooms as $classroom)
                                <tr>
                                    <td class="px-4 py-3 text-slate-900">
                                        {{ $classroom->year }}{{ $classroom->name }} {{ $classroom->section }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">{{ $classroom->students_count }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('teacher.classes.show', $classroom->id) }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
                                            Visualizza
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
