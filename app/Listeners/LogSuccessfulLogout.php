<?php

namespace App\Listeners;

use App\Support\ActivityLogger;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    public function handle(Logout $event): void
    {
        $user = $event->user;

        if (! $user) {
            return;
        }

        ActivityLogger::log(
            'logout',
            'Logout: '.($user->name ?? $user->username ?? 'Pengguna'),
            'auth',
        );
    }
}
