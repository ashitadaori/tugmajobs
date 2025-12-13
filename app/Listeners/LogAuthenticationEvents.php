<?php

namespace App\Listeners;

use App\Models\SecurityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Events\Dispatcher;

class LogAuthenticationEvents
{
    /**
     * Handle user login events.
     */
    public function handleLogin(Login $event): void
    {
        SecurityLog::logEvent(
            'login',
            $event->user->id,
            'success',
            "User {$event->user->name} ({$event->user->role}) logged in successfully"
        );
    }

    /**
     * Handle user logout events.
     */
    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            SecurityLog::logEvent(
                'logout',
                $event->user->id,
                'success',
                "User {$event->user->name} logged out"
            );
        }
    }

    /**
     * Handle failed login attempts.
     */
    public function handleFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? 'unknown';
        
        SecurityLog::logEvent(
            'failed_login',
            null,
            'failed',
            "Failed login attempt for: {$email}"
        );
    }

    /**
     * Handle account lockout events.
     */
    public function handleLockout(Lockout $event): void
    {
        $email = $event->request->input('email', 'unknown');
        
        SecurityLog::logEvent(
            'failed_login',
            null,
            'blocked',
            "Account locked out after multiple failed attempts: {$email}"
        );
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
            Lockout::class => 'handleLockout',
        ];
    }
}
