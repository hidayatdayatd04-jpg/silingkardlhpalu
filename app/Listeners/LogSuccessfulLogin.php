<?php

namespace App\Listeners;

use App\Support\ActivityLogger;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        ActivityLogger::log(
            'login',
            'Login: '.($user->name ?? $user->username ?? 'Pengguna'),
            'auth',
        );
    }
}
