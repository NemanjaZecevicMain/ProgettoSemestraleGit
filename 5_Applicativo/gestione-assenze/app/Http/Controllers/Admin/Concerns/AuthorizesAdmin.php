<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\User;

trait AuthorizesAdmin
{
    private function ensureAdmin(?User $user): void
    {
        if (!$user || $user->role !== 'ADMIN') {
            abort(403);
        }
    }
}
