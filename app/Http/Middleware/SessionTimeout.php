<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Session inactivity timeout in seconds.
     * Configurable via SESSION_TIMEOUT in .env (default 30 minutes).
     */
    private int $timeout;

    public function __construct()
    {
        $this->timeout = (int) config('session.timeout_seconds', 1800);
    }

    /**
     * Expire the session after the configured inactivity period.
     * The last activity timestamp is updated on every request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = $request->session()->get('last_activity_at');

            if ($lastActivity !== null && (time() - $lastActivity) > $this->timeout) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'A sua sessão expirou por inactividade. Por favor inicie sessão novamente.']);
            }

            // Refresh the last activity timestamp on every authenticated request
            $request->session()->put('last_activity_at', time());
        }

        return $next($request);
    }
}
