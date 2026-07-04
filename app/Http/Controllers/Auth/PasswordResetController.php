<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a password reset link to the given e-mail.
     * Always return the same generic response to prevent e-mail enumeration.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:180'],
        ]);

        // sendResetLink handles user lookup internally — we never reveal existence
        Password::sendResetLink($request->only('email'));

        return back()->with(
            'status',
            'Se o endereço de e-mail estiver registado, receberá um link de recuperação em breve.'
        );
    }

    /**
     * Show the password reset form.
     */
    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Reset the user's password.
     */
    public function reset(PasswordResetRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) use ($request) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Audit the password reset event
                AuditLog::create([
                    'user_id'     => $user->id,
                    'action'      => AuditLog::ACTION_PASSWORD_RESET,
                    'entity_type' => \App\Models\User::class,
                    'entity_id'   => $user->id,
                    'ip_address'  => $request->ip(),
                    'user_agent'  => $request->userAgent(),
                ]);

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Senha redefinida com sucesso. Pode iniciar sessão.')
            : back()->withErrors(['email' => [__($status)]]);
    }
}
