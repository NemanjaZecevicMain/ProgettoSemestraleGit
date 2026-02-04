<x-guest-layout>
    <div class="text-center mb-6">
        <div class="text-sm uppercase tracking-wider text-blue-700">
            SAMT Trevano
        </div>
        <h1 class="text-2xl font-semibold text-slate-900">
            Accedi
        </h1>
        <p class="text-sm text-slate-600">
            Gestione assenze scolastiche
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- EMAIL -->
        <div>
            <x-input-label for="email" value="Email" />
            <div class="relative mt-1">
                <span class="pointer-events-none absolute left-0 top-0 h-11 w-11 flex items-center justify-center text-blue-700">
                    <svg class="w-5 h-5 block" viewBox="0 0 24 24" fill="none">
                        <path d="M4 6h16v12H4z" stroke="currentColor" stroke-width="2"/>
                        <path d="M4 7l8 6 8-6"
                              stroke="currentColor"
                              stroke-width="2"
                              stroke-linecap="round"/>
                    </svg>
                </span>

                <x-text-input
                    id="email"
                    name="email"
                    type="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                    class="block w-full h-11 box-border pr-4"
                    style="padding-left: 3.75rem;"
                />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- PASSWORD -->
        <div class="mt-4">
            <x-input-label for="password" value="Password" />
            <div class="relative mt-1">
                <span class="pointer-events-none absolute left-0 top-0 h-11 w-11 flex items-center justify-center text-blue-700">
                    <svg class="w-5 h-5 block" viewBox="0 0 24 24" fill="none">
                        <rect x="5" y="10" width="14" height="10" rx="2"
                              stroke="currentColor"
                              stroke-width="2"/>
                        <path d="M8 10V7a4 4 0 0 1 8 0v3"
                              stroke="currentColor"
                              stroke-width="2"
                              stroke-linecap="round"/>
                    </svg>
                </span>

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="block w-full h-11 box-border pr-4"
                    style="padding-left: 3.75rem;"
                />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- REMEMBER ME -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="rounded border-gray-300 text-blue-700 shadow-sm focus:ring-blue-500"
                >
                <span class="ms-2 text-sm text-gray-600">
                    Ricordami
                </span>
            </label>
        </div>

        <!-- ACTIONS -->
        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    href="{{ route('password.request') }}"
                >
                    Password dimenticata?
                </a>
            @endif

            <x-primary-button class="ms-3">
                Entra
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
