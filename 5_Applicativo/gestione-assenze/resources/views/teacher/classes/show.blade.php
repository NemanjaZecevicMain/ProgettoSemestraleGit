<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">
                    Classe {{ $classroom->year }}{{ $classroom->name }} {{ $classroom->section }}
                </h1>
                <p class="text-sm text-slate-500">Elenco studenti della classe.</p>
            </div>
            <a href="{{ route('teacher.classes.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
                Torna alle classi
            </a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @if ($students->isEmpty())
                <div class="text-sm text-slate-500">Nessuno studente in questa classe.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Studente</th>
                                <th class="px-4 py-3 text-left font-medium">Email</th>
                                <th class="px-4 py-3 text-right font-medium">Profilo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($students as $student)
                                <tr>
                                    <td class="px-4 py-3 text-slate-900">{{ $student->name }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $student->email }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('teacher.students.show', $student->id) }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
                                            Apri
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
