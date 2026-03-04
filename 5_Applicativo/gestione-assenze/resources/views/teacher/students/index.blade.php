<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Tutti gli studenti</h1>
            <p class="text-sm text-slate-500">Elenco completo degli studenti dell'istituto.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="flex flex-col gap-1">
                    <label for="q" class="text-xs font-medium text-slate-600">Nome o email</label>
                    <input
                        id="q"
                        name="q"
                        type="text"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="Cerca studente"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label for="classroom_id" class="text-xs font-medium text-slate-600">Classe</label>
                    <select
                        id="classroom_id"
                        name="classroom_id"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="">Tutte le classi</option>
                        @foreach ($classrooms as $classroom)
                            @php
                                $label = $classroom->year . $classroom->name . ' ' . $classroom->section;
                            @endphp
                            <option value="{{ $classroom->id }}" @selected(($filters['classroom_id'] ?? '') == $classroom->id)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label for="year" class="text-xs font-medium text-slate-600">Anno</label>
                    <input
                        id="year"
                        name="year"
                        type="number"
                        min="1"
                        max="5"
                        value="{{ $filters['year'] ?? '' }}"
                        placeholder="Es. 3"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label for="section" class="text-xs font-medium text-slate-600">Sezione</label>
                    <input
                        id="section"
                        name="section"
                        type="text"
                        maxlength="2"
                        value="{{ $filters['section'] ?? '' }}"
                        placeholder="Es. A"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm uppercase text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                    />
                </div>

                <div class="flex items-center gap-3 md:col-span-4">
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Filtra
                    </button>
                    <a href="{{ route('teacher.students.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
                        Rimuovi filtri
                    </a>
                </div>
            </form>

            @if ($students->isEmpty())
                <div class="text-sm text-slate-500">Nessuno studente presente.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Studente</th>
                                <th class="px-4 py-3 text-left font-medium">Classe</th>
                                <th class="px-4 py-3 text-left font-medium">Email</th>
                                <th class="px-4 py-3 text-right font-medium">Profilo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($students as $student)
                                @php
                                    $classroomLabel = $student->classroom
                                        ? $student->classroom->year . $student->classroom->name . ' ' . $student->classroom->section
                                        : '-';
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-slate-900">{{ $student->name }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $classroomLabel }}</td>
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
