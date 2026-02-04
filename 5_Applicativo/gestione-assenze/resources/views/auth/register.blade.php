<x-guest-layout>
    <div class="text-center mb-6">
        <div class="text-sm uppercase tracking-wider text-blue-700">SAMT Trevano</div>
        <h1 class="text-2xl font-semibold text-slate-900">Crea account studente</h1>
        <p class="text-sm text-slate-600">Email generata automaticamente da nome e cognome.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" id="registerForm">
        @csrf

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- NOME -->
        <div>
            <x-input-label for="first_name" value="Nome" />
            <div class="relative mt-1">
                <span class="pointer-events-none absolute left-0 top-0 h-11 w-11 flex items-center justify-center text-blue-700">
                    <svg class="w-5 h-5 block" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/>
                        <path d="M4 20c2-4 14-4 16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>

                <x-text-input
                    id="first_name"
                    name="first_name"
                    type="text"
                    :value="old('first_name')"
                    required
                    autofocus
                    autocomplete="given-name"
                    class="block w-full h-11 box-border pr-4"
                    style="padding-left: 3.75rem;"
                />
            </div>
            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
        </div>

        <!-- COGNOME -->
        <div>
            <x-input-label for="last_name" value="Cognome" />
            <div class="relative mt-1">
                <span class="pointer-events-none absolute left-0 top-0 h-11 w-11 flex items-center justify-center text-blue-700">
                    <svg class="w-5 h-5 block" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/>
                        <path d="M4 20c2-4 14-4 16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>

                <x-text-input
                    id="last_name"
                    name="last_name"
                    type="text"
                    :value="old('last_name')"
                    required
                    autocomplete="family-name"
                    class="block w-full h-11 box-border pr-4"
                    style="padding-left: 3.75rem;"
                />
            </div>
            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
        </div>
    </div>




    <div class="mt-4">
        <x-input-label for="email_preview" value="Email (generata automaticamente)" />
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

            <input
                id="email_preview"
                type="text"
                readonly
                value="nome.cognome@samtrevano.ch"
                class="block w-full h-11 box-border pr-4 border-gray-300 rounded-md shadow-sm bg-slate-50 text-slate-700"
                style="padding-left: 3.75rem;"
            />
        </div>

        <p class="text-xs text-slate-500 mt-1">
            Se l'email esiste già, verrà aggiunto un numero progressivo.
        </p>
    </div>


        <div class="mt-4">
            <x-input-label for="classroom_id" value="Classe" />
            <select id="classroom_id" name="classroom_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                <option value="">Seleziona classe</option>
                @foreach($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected(old('classroom_id') == $classroom->id)>
                        {{ $classroom->year }}{{ $classroom->name }} {{ $classroom->section }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('classroom_id')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_minor" value="1" class="rounded border-gray-300"
                    @checked(old('is_minor'))>
                <span class="ml-2 text-sm text-gray-600">Studente minorenne</span>
            </label>
            <x-input-error :messages="$errors->get('is_minor')" class="mt-2" />
        </div>

        <div class="mt-4">
    <x-input-label for="password" value="Password" />
        <div class="relative mt-1">
            <span class="pointer-events-none absolute left-0 top-0 h-11 w-11 flex items-center justify-center text-blue-700">
                <svg class="w-5 h-5 block" viewBox="0 0 24 24" fill="none">
                    <rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"/>
                </svg>
            </span>

            <x-text-input
                id="password"
                class="block w-full h-11 box-border pr-4"
                style="padding-left: 3.75rem;"
                type="password"
                name="password"
                required
                autocomplete="new-password"
            />
        </div>
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div class="mt-2">
        <div class="h-2 w-full bg-slate-100 rounded">
            <div id="strengthBar" class="h-2 rounded bg-red-400" style="width: 0%"></div>
        </div>
        <div id="strengthLabel" class="text-xs mt-1 text-slate-500">
            Forza password: debole
        </div>
    </div>

    <div class="mt-3 text-sm">
        <div class="font-medium text-slate-700 mb-1">Requisiti password</div>
        <ul class="space-y-1 text-slate-600">
            <li id="ruleLength">NO Almeno 10 caratteri</li>
            <li id="ruleUpper">NO Almeno 1 maiuscola</li>
            <li id="ruleLower">NO Almeno 1 minuscola</li>
            <li id="ruleNumber">NO Almeno 1 numero</li>
            <li id="ruleSymbol">NO Almeno 1 simbolo</li>
        </ul>
    </div>

    <div class="mt-4">
        <x-input-label for="password_confirmation" value="Conferma Password" />
        <div class="relative mt-1">
            <span class="pointer-events-none absolute left-0 top-0 h-11 w-11 flex items-center justify-center text-blue-700">
                <svg class="w-5 h-5 block" viewBox="0 0 24 24" fill="none">
                    <rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"/>
                </svg>
            </span>

            <x-text-input
                id="password_confirmation"
                class="block w-full h-11 box-border pr-4"
                style="padding-left: 3.75rem;"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />
        </div>
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>


        <div class="flex items-center justify-end mt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" href="{{ route('login') }}">
                Hai gia un account?
            </a>

            <x-primary-button class="ms-4">
                Registrati
            </x-primary-button>
        </div>
    </form>

    <script>
        const firstName = document.getElementById('first_name');
        const lastName = document.getElementById('last_name');
        const emailPreview = document.getElementById('email_preview');
        const passwordInput = document.getElementById('password');

        function normalizePart(value) {
            return value
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '');
        }

        function updateEmailPreview() {
            const first = normalizePart(firstName.value || 'nome');
            const last = normalizePart(lastName.value || 'cognome');
            const local = `${first}.${last}`;
            emailPreview.value = `${local}@samtrevano.ch`;
        }

        function setRule(el, ok) {
            const label = el.textContent.replace(/^OK |^NO /, '');
            el.textContent = (ok ? 'OK ' : 'NO ') + label;
            el.classList.toggle('text-green-700', ok);
            el.classList.toggle('text-slate-600', !ok);
        }

        function updateStrength() {
            const v = passwordInput.value || '';
            const rules = {
                length: v.length >= 10,
                upper: /[A-Z]/.test(v),
                lower: /[a-z]/.test(v),
                number: /[0-9]/.test(v),
                symbol: /[^A-Za-z0-9]/.test(v),
            };

            setRule(document.getElementById('ruleLength'), rules.length);
            setRule(document.getElementById('ruleUpper'), rules.upper);
            setRule(document.getElementById('ruleLower'), rules.lower);
            setRule(document.getElementById('ruleNumber'), rules.number);
            setRule(document.getElementById('ruleSymbol'), rules.symbol);

            const score = Object.values(rules).filter(Boolean).length;
            const bar = document.getElementById('strengthBar');
            const label = document.getElementById('strengthLabel');

            const pct = (score / 5) * 100;
            bar.style.width = pct + '%';

            if (score <= 2) {
                bar.className = 'h-2 rounded bg-red-400';
                label.textContent = 'Forza password: debole';
            } else if (score <= 4) {
                bar.className = 'h-2 rounded bg-yellow-400';
                label.textContent = 'Forza password: media';
            } else {
                bar.className = 'h-2 rounded bg-green-500';
                label.textContent = 'Forza password: forte';
            }
        }

        firstName.addEventListener('input', updateEmailPreview);
        lastName.addEventListener('input', updateEmailPreview);
        passwordInput.addEventListener('input', updateStrength);

        updateEmailPreview();
        updateStrength();
    </script>
</x-guest-layout>
