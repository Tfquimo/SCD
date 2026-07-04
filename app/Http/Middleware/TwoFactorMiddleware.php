<?php

namespace App\Http\Middleware;

use App\Services\Auth\TwoFactorService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    public function __construct(
        private readonly TwoFactorService $twoFactorService
    ) {}

    /**
     * Redirect authenticated users to the 2FA challenge if:
     *  - They have 2FA confirmed (enabled), AND
     *  - The current session has NOT been marked as 2FA-verified.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->hasVerified2FA()
            && ! $this->twoFactorService->sessionIsVerified($request)
        ) {
            // Allow the 2FA challenge routes through without redirect loop
            if ($request->routeIs('two-factor.*')) {
                return $next($request);
            }

            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
