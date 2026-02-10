<x-app-sidebar>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Profilo</h1>
            <p class="mt-1 text-sm text-slate-500">Gestisci dati personali, password e sicurezza account.</p>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-sidebar>
