<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Classroom;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', [
            'classrooms' => Classroom::orderBy('year')->orderBy('section')->get(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(10)->mixedCase()->numbers()->symbols(),
            ],
            'classroom_id' => ['required', 'exists:classroom,id'],
            'is_minor' => ['nullable', 'boolean'],
        ], [
            'first_name.required' => 'Il nome e obbligatorio.',
            'last_name.required' => 'Il cognome e obbligatorio.',
            'password.min' => 'La password deve avere almeno 10 caratteri.',
            'password.mixed' => 'La password deve includere una maiuscola e una minuscola.',
            'password.numbers' => 'La password deve includere almeno un numero.',
            'password.symbols' => 'La password deve includere almeno un simbolo.',
            'classroom_id.required' => 'Seleziona una classe.',
            'classroom_id.exists' => 'La classe selezionata non e valida.',
        ]);

        $firstName = trim($request->first_name);
        $lastName = trim($request->last_name);
        $fullName = $firstName . ' ' . $lastName;

        $email = $this->generateUniqueEmail($firstName, $lastName);

        $user = User::create([
            'name' => $fullName,
            'email' => $email,
            'password_hash' => Hash::make($request->password),
            'role' => 'STUDENT',
            'is_minor' => $request->boolean('is_minor'),
            'classroom_id' => $request->classroom_id,
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    private function generateUniqueEmail(string $firstName, string $lastName): string
    {
        $baseFirst = $this->normalizeNamePart($firstName);
        $baseLast = $this->normalizeNamePart($lastName);
        $baseLocal = $baseFirst . '.' . $baseLast;
        $domain = 'samtrevano.ch';

        $email = $baseLocal . '@' . $domain;
        $suffix = 2;

        while (User::where('email', $email)->exists()) {
            $email = $baseLocal . $suffix . '@' . $domain;
            $suffix++;
        }

        return $email;
    }

    private function normalizeNamePart(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }
}
