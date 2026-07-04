<?php

namespace App\Services\Auth;

use App\Models\AuditLog;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{
    private const MAX_ATTEMPTS     = 5;
    private const LOCK_MINUTES     = 30;

    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * Attempt to authenticate a user.
     * Returns the authenticated User on success.
     * Throws ValidationException on failure (generic message to prevent enumeration).
     */
    public function attempt(Request $request): User
    {
        $email    = $request->input('email');
        $password = $request->input('password');

        $user = $this->userRepository->findByEmail($email);

        // Generic failure — same response whether user exists or not
        if (! $user || ! Hash::check($password, $user->password)) {
            $this->handleFailedAttempt($user, $request);

            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (! $user->isActive()) {
            $this->audit(AuditLog::ACTION_LOGIN_FAILED, $request, $user, [
                'reason' => 'account_inactive',
            ]);
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if ($user->isLocked()) {
            $this->audit(AuditLog::ACTION_LOGIN_FAILED, $request, $user, [
                'reason'       => 'account_locked',
                'locked_until' => $user->locked_until->toISOString(),
            ]);
            throw ValidationException::withMessages([
                'email' => [__('auth.throttle', [
                    'seconds' => Carbon::now()->diffInSeconds($user->locked_until),
                    'minutes' => self::LOCK_MINUTES,
                ])],
            ]);
        }

        // Successful credential check — reset counter and update login info
        $this->userRepository->resetFailedAttempts($user);
        $this->userRepository->updateLastLogin($user, $request->ip());

        // Prevent session fixation: regenerate session ID after login
        $request->session()->regenerate();

        $this->audit(AuditLog::ACTION_LOGIN, $request, $user);

        return $user;
    }

    /**
     * Log the user out and destroy their session.
     */
    public function logout(Request $request): void
    {
        /** @var User|null $user */
        $user = $request->user();

        $this->audit(AuditLog::ACTION_LOGOUT, $request, $user);

        auth()->logout();

        // Fully invalidate session to prevent reuse
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Handle a failed login attempt — increment counter and lock if threshold reached.
     */
    private function handleFailedAttempt(?User $user, Request $request): void
    {
        if (! $user) {
            // Still audit unknown email attempts to detect enumeration
            $this->auditRaw(AuditLog::ACTION_LOGIN_FAILED, $request, null, [
                'email'  => $request->input('email'),
                'reason' => 'user_not_found',
            ]);
            return;
        }

        $this->userRepository->incrementFailedAttempts($user);
        $user->refresh();

        $this->audit(AuditLog::ACTION_LOGIN_FAILED, $request, $user, [
            'attempts' => $user->failed_login_attempts,
        ]);

        if ($user->failed_login_attempts >= self::MAX_ATTEMPTS) {
            $lockedUntil = Carbon::now()->addMinutes(self::LOCK_MINUTES);
            $this->userRepository->lockAccount($user, $lockedUntil);

            $this->audit(AuditLog::ACTION_ACCOUNT_LOCKED, $request, $user, [
                'locked_until' => $lockedUntil->toISOString(),
            ]);

            Log::warning('Account locked due to repeated failures', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'ip'      => $request->ip(),
            ]);
        }
    }

    /**
     * Write an audit log entry for an authenticated user.
     */
    private function audit(string $action, Request $request, ?User $user, array $metadata = []): void
    {
        AuditLog::create([
            'user_id'     => $user?->id,
            'action'      => $action,
            'entity_type' => User::class,
            'entity_id'   => $user?->id,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'metadata'    => $metadata ?: null,
        ]);
    }

    /**
     * Write an audit log entry without a resolved user (e.g., unknown email).
     */
    private function auditRaw(string $action, Request $request, ?int $userId, array $metadata = []): void
    {
        AuditLog::create([
            'user_id'    => $userId,
            'action'     => $action,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata'   => $metadata ?: null,
        ]);
    }
}
