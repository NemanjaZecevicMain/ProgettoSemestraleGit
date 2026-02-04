<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SAMT Trevano') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root { --brand: #1e40af; }
            .school-bg {
                background:
                    radial-gradient(800px 400px at 10% 10%, rgba(59,130,246,0.12), transparent 60%),
                    radial-gradient(700px 350px at 90% 20%, rgba(30,64,175,0.10), transparent 60%),
                    linear-gradient(135deg, #f8fbff 0%, #ffffff 45%, #eaf2ff 100%);
            }
            .floaty { animation: floaty 6s ease-in-out infinite; }
            .floaty-slow { animation: floaty 10s ease-in-out infinite; }
            @keyframes floaty {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-8px); }
            }
        </style>
    </head>
    <body class="school-bg min-h-screen font-[Manrope] text-slate-900 antialiased">
        <div class="relative min-h-screen flex items-center justify-center px-4 py-10 overflow-hidden">
            <div class="absolute -top-6 left-6 opacity-40 floaty">
                <svg width="56" height="56" viewBox="0 0 64 64" fill="none">
                    <rect x="10" y="10" width="44" height="44" rx="8" fill="#bfdbfe"/>
                    <path d="M20 24h24M20 32h24M20 40h16" stroke="#1e3a8a" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="absolute top-20 right-8 opacity-40 floaty-slow">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
                    <path d="M16 44l28-28 4 4-28 28H16v-4z" fill="#93c5fd"/>
                    <path d="M20 40l24-24" stroke="#1e3a8a" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="absolute bottom-10 left-10 opacity-40 floaty">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
                    <circle cx="32" cy="32" r="20" fill="#dbeafe"/>
                    <path d="M24 34l6 6 12-16" stroke="#1e3a8a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <div class="w-full sm:max-w-md">
                <div class="bg-white/90 backdrop-blur-sm shadow-xl rounded-2xl border border-blue-100 px-6 py-6 animate-[fadeIn_0.6s_ease-out]">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
