@php
    $user = auth()->user();
    $isStudent = $user?->role === 'STUDENT';
@endphp

<x-app-sidebar>
    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-900 via-blue-800 to-blue-700 p-7 text-white shadow-lg">
            <p class="text-xs uppercase tracking-[0.18em] text-blue-200">Dashboard</p>
            <h1 class="mt-2 text-3xl font-semibold">Benvenuto, {{ $user->name }}</h1>
            <p class="mt-3 max-w-3xl text-sm text-blue-100">
                Area personale per consultare informazioni scolastiche, gestire il profilo e monitorare lo stato delle richieste.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                @if ($isStudent)
                    <a href="{{ route('student.absences.index') }}" class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-blue-900 hover:bg-blue-100 transition-colors">
                        Vai alle assenze
                    </a>
                @endif
                <a href="{{ route('settings.index') }}" class="inline-flex items-center rounded-lg border border-white/40 px-4 py-2 text-sm font-medium text-white hover:bg-white/10 transition-colors">
                    Apri impostazioni
                </a>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">Ruolo</p>
                <p class="mt-2 text-lg font-semibold text-blue-900">{{ $user->role }}</p>
            </article>
            <article class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">Email</p>
                <p class="mt-2 truncate text-sm font-medium text-slate-900">{{ $user->email }}</p>
            </article>
            <article class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">Classe</p>
                <p class="mt-2 text-sm font-medium text-slate-900">
                    @if ($user->classroom)
                        {{ $user->classroom->year . $user->classroom->name . ' ' . $user->classroom->section }}
                    @else
                        Non assegnata
                    @endif
                </p>
            </article>
            <article class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">Account</p>
                <p class="mt-2 text-sm font-medium text-emerald-700">Attivo</p>
            </article>
        </section>
    </div>
</x-app-sidebar>
