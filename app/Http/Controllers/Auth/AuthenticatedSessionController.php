<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Display the login form.
     */
    public function create(): View|RedirectResponse
    {
        // Already authenticated — redirect away from login
        if (auth()->check()) {
            return redirect()->intended(route('dashboard'));
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     * All business logic is delegated to AuthService.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Rate limiting: 5 attempts per minute per IP+email combination
        $this->ensureIsNotRateLimited($request);

        $user = $this->authService->attempt($request);

        // Clear rate limiter on success
        \Illuminate\Support\Facades\RateLimiter::clear($this->throttleKey($request));

        auth()->login($user, $request->boolean('remember'));

        // Redirect to 2FA if the user has it enabled and not yet verified
        if ($user->hasVerified2FA()) {
            return redirect()->route('two-factor.challenge');
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy the authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->authService->logout($request);

        return redirect()->route('login');
    }

    /**
     * Ensure the login request is not rate limited.
     */
    private function ensureIsNotRateLimited(LoginRequest $request): void
    {
        $key = $this->throttleKey($request);

        if (! \Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 5)) {
            \Illuminate\Support\Facades\RateLimiter::hit($key, 60);
            return;
        }

        $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);

        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Unique throttle key combining IP address and e-mail.
     */
    private function throttleKey(LoginRequest $request): string
    {
        return \Illuminate\Support\Str::transliterate(
            \Illuminate\Support\Str::lower($request->input('email')) . '|' . $request->ip()
        );
    }
}
