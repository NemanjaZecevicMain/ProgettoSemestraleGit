<x-app-sidebar>
    @php
        $classroomLabel = $student->classroom
            ? $student->classroom->year . $student->classroom->name . ' ' . $student->classroom->section
            : '-';
        $guardianLabel = $student->guardian?->name ?? '-';
        $guardianEmail = $student->guardian?->email ?? '-';
        $birthdate = $student->date_of_birth ? $student->date_of_birth->format('d.m.Y') : '-';
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $student->name }}</h1>
                <p class="text-sm text-slate-500">Profilo studente</p>
            </div>
            <a href="{{ route('teacher.students.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
                Torna all'elenco
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase tracking-wider text-slate-500">Dati principali</div>
                <div class="mt-3 space-y-2 text-sm text-slate-700">
                    <div><span class="font-medium text-slate-900">Email:</span> {{ $student->email }}</div>
                    <div><span class="font-medium text-slate-900">Classe:</span> {{ $classroomLabel }}</div>
                    <div><span class="font-medium text-slate-900">Data di nascita:</span> {{ $birthdate }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase tracking-wider text-slate-500">Tutore legale</div>
                <div class="mt-3 space-y-2 text-sm text-slate-700">
                    <div><span class="font-medium text-slate-900">Nome:</span> {{ $guardianLabel }}</div>
                    <div><span class="font-medium text-slate-900">Email:</span> {{ $guardianEmail }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-sidebar>
