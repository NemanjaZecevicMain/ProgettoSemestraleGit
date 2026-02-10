<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CPT Lugano-Trevano') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gradient-to-br from-blue-50 via-slate-50 to-white text-slate-900 antialiased">
        @php
            $activeLink = 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium bg-white text-blue-900 shadow-sm';
            $inactiveLink = 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-blue-100 hover:bg-white/10 hover:text-white transition-colors';
            $activeIcon = 'h-5 w-5 text-blue-800';
            $inactiveIcon = 'h-5 w-5 text-blue-200 group-hover:text-white transition-colors';
            $isDashboard = request()->routeIs('dashboard');
            $isAbsences = request()->routeIs('student.absences.*');
            $isDelays = request()->routeIs('student.delays.*');
            $isSettings = request()->routeIs('settings.*');
            $isStudent = auth()->user()?->role === 'STUDENT';
            $user = auth()->user();
        @endphp

        <div class="min-h-screen flex flex-col lg:flex-row">
            <aside class="w-full lg:w-64 lg:shrink-0 bg-gradient-to-b from-blue-900 via-blue-800 to-blue-900 border-b lg:border-b-0 lg:border-r border-blue-700/40 shadow-xl lg:sticky lg:top-0 lg:h-screen">
                <div class="flex h-full flex-col">
                    <div class="px-6 py-6 border-b border-blue-700/50">
                        <div class="text-lg font-semibold text-white">CPT Lugano-Trevano</div>
                        <div class="mt-1 text-sm text-blue-200">Area riservata</div>
                    </div>

                    <nav class="flex-1 px-3 py-4 space-y-1">
                        <a href="{{ route('dashboard') }}" class="{{ $isDashboard ? $activeLink : $inactiveLink }}">
                            <svg class="{{ $isDashboard ? $activeIcon : $inactiveIcon }}" viewBox="0 0 24 24" fill="none">
                                <path d="M4 10l8-6 8 6v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8z" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            Dashboard
                        </a>
                        @if ($isStudent)
                            <a href="{{ route('student.absences.index') }}" class="{{ $isAbsences ? $activeLink : $inactiveLink }}">
                                <svg class="{{ $isAbsences ? $activeIcon : $inactiveIcon }}" viewBox="0 0 24 24" fill="none">
                                    <path d="M7 4h10a2 2 0 0 1 2 2v14l-7-3-7 3V6a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                Le mie assenze
                            </a>
                        @endif
                        @if ($isStudent)
                            <a href="{{ route('student.delays.index') }}" class="{{ $isDelays ? $activeLink : $inactiveLink }}">
                                <svg class="{{ $isDelays ? $activeIcon : $inactiveIcon }}" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 7v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                I miei ritardi
                            </a>
                        @endif
                        <a href="#" class="{{ $inactiveLink }}">
                            <svg class="{{ $inactiveIcon }}" viewBox="0 0 24 24" fill="none">
                                <rect x="4" y="3" width="16" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                                <path d="M8 7h8M8 11h8M8 15h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Certificati
                        </a>
                        <a href="{{ route('settings.index') }}" class="{{ $isSettings ? $activeLink : $inactiveLink }}">
                            <svg class="{{ $isSettings ? $activeIcon : $inactiveIcon }}" viewBox="0 0 24 24" fill="none">
                                <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" stroke="currentColor" stroke-width="2"/>
                                <path d="M19.4 15a1 1 0 0 0 .2 1.1l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1 1 0 0 0-1.1-.2 1 1 0 0 0-.6.9V20a2 2 0 0 1-4 0v-.1a1 1 0 0 0-.6-.9 1 1 0 0 0-1.1.2l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1 1 0 0 0 .2-1.1 1 1 0 0 0-.9-.6H4a2 2 0 0 1 0-4h.1a1 1 0 0 0 .9-.6 1 1 0 0 0-.2-1.1l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1 1 0 0 0 1.1.2 1 1 0 0 0 .6-.9V4a2 2 0 0 1 4 0v.1a1 1 0 0 0 .6.9 1 1 0 0 0 1.1-.2l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1 1 0 0 0-.2 1.1 1 1 0 0 0 .9.6H20a2 2 0 0 1 0 4h-.1a1 1 0 0 0-.9.6z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Impostazioni
                        </a>
                        <a href="#" class="{{ $inactiveLink }}">
                            <svg class="{{ $inactiveIcon }}" viewBox="0 0 24 24" fill="none">
                                <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Stato firme
                        </a>
                    </nav>

                    <div class="px-3 py-4 border-t border-blue-700/50">
                        <div class="rounded-xl bg-white/10 px-3 py-3">
                            <div class="text-sm font-medium text-white">{{ $user?->name }}</div>
                            <div class="text-xs uppercase tracking-wide text-blue-200">{{ $user?->role }}</div>
                            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                                @csrf
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-white px-3 py-2 text-sm font-medium text-blue-900 hover:bg-blue-100 transition-colors">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="flex-1 min-w-0 px-4 py-5 sm:px-6 lg:px-10 lg:py-8">
                <div class="mx-auto w-full max-w-7xl">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
