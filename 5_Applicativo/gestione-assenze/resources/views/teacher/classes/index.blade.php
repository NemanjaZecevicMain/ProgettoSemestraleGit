<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Le mie classi</h1>
            <p class="text-sm text-slate-500">Classi assegnate al docente.</p>
        </div>

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
