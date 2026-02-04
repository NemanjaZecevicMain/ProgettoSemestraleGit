<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CPT Lugano-Trevano') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-50 text-slate-900 antialiased">
        <div class="min-h-screen flex flex-col lg:flex-row">
            <aside class="w-full lg:w-72 bg-white border-b lg:border-b-0 lg:border-r border-slate-200">
                <div class="px-6 py-5 border-b border-slate-200">
                    <div class="text-lg font-semibold text-blue-800">CPT Lugano-Trevano</div>
                    <div class="text-sm text-slate-500">Istituto scolastico</div>
                </div>

                <nav class="px-3 py-4 space-y-1">
                    <a href="{{ route('dashboard') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-800">
                        <svg class="h-5 w-5 text-slate-400 group-hover:text-blue-700" viewBox="0 0 24 24" fill="none">
                            <path d="M4 10l8-6 8 6v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        Dashboard
                    </a>
                    <a href="#" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-800">
                        <svg class="h-5 w-5 text-slate-400 group-hover:text-blue-700" viewBox="0 0 24 24" fill="none">
                            <path d="M7 4h10a2 2 0 0 1 2 2v14l-7-3-7 3V6a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        Le mie assenze
                    </a>
                    <a href="#" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-800">
                        <svg class="h-5 w-5 text-slate-400 group-hover:text-blue-700" viewBox="0 0 24 24" fill="none">
                            <path d="M12 7v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        I miei ritardi
                    </a>
                    <a href="#" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-800">
                        <svg class="h-5 w-5 text-slate-400 group-hover:text-blue-700" viewBox="0 0 24 24" fill="none">
                            <rect x="4" y="3" width="16" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                            <path d="M8 7h8M8 11h8M8 15h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Certificati
                    </a>
                    <a href="{{ route('settings.index') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium bg-blue-50 text-blue-800 border-l-4 border-blue-700">
                        <svg class="h-5 w-5 text-blue-700" viewBox="0 0 24 24" fill="none">
                            <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" stroke="currentColor" stroke-width="2"/>
                            <path d="M19.4 15a1 1 0 0 0 .2 1.1l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1 1 0 0 0-1.1-.2 1 1 0 0 0-.6.9V20a2 2 0 0 1-4 0v-.1a1 1 0 0 0-.6-.9 1 1 0 0 0-1.1.2l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1 1 0 0 0 .2-1.1 1 1 0 0 0-.9-.6H4a2 2 0 0 1 0-4h.1a1 1 0 0 0 .9-.6 1 1 0 0 0-.2-1.1l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1 1 0 0 0 1.1.2 1 1 0 0 0 .6-.9V4a2 2 0 0 1 4 0v.1a1 1 0 0 0 .6.9 1 1 0 0 0 1.1-.2l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1 1 0 0 0-.2 1.1 1 1 0 0 0 .9.6H20a2 2 0 0 1 0 4h-.1a1 1 0 0 0-.9.6z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Impostazioni
                    </a>
                    <a href="#" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-800">
                        <svg class="h-5 w-5 text-slate-400 group-hover:text-blue-700" viewBox="0 0 24 24" fill="none">
                            <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Stato firme
                    </a>
                </nav>
            </aside>

            <main class="flex-1 px-6 py-6 lg:px-10 lg:py-8">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
