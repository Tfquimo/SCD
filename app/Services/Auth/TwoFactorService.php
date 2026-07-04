<?php

namespace App\Services\Auth;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FALaravel\Google2FA;

class TwoFactorService
{
    public function __construct(
        private readonly Google2FA $google2fa
    ) {}

    /**
     * Generate a new 2FA secret for the user and store it encrypted.
     * The secret is NOT confirmed until the user verifies their first OTP.
     */
    public function generateSecret(User $user): string
    {
        $secret = $this->google2fa->generateSecretKey();

        $user->update([
            'two_factor_secret'        => Crypt::encryptString($secret),
            'two_factor_confirmed_at'  => null, // require explicit confirmation
        ]);

        return $secret;
    }

    /**
     * Build the QR code URL for the Google Authenticator app.
     */
    public function getQrCodeUrl(User $user): string
    {
        $secret = $this->decryptSecret($user);

        $otpauth = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        // Generate the QR code SVG locally/offline
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        $svg = $writer->writeString($otpauth);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Verify an OTP code supplied by the user.
     */
    public function verify(User $user, string $code): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        $secret = $this->decryptSecret($user);

        return (bool) $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * Confirm and enable 2FA after the user has verified their first OTP.
     */
    public function enable(User $user, string $code, Request $request): bool
    {
        if (! $this->verify($user, $code)) {
            $this->audit(AuditLog::ACTION_2FA_FAILED, $request, $user, [
                'stage' => 'enable',
            ]);
            return false;
        }

        $user->update(['two_factor_confirmed_at' => now()]);

        $this->audit(AuditLog::ACTION_2FA_ENABLED, $request, $user);

        return true;
    }

    /**
     * Disable 2FA and wipe secret from the database.
     */
    public function disable(User $user, Request $request): void
    {
        $user->update([
            'two_factor_secret'       => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        $this->audit(AuditLog::ACTION_2FA_DISABLED, $request, $user);
    }

    /**
     * Mark the current session as having passed 2FA verification.
     */
    public function markSessionVerified(Request $request): void
    {
        $request->session()->put('auth.2fa_verified', true);
    }

    /**
     * Check whether the current session has passed 2FA.
     */
    public function sessionIsVerified(Request $request): bool
    {
        return $request->session()->get('auth.2fa_verified', false) === true;
    }

    /**
     * Decrypt the user's stored 2FA secret.
     */
    private function decryptSecret(User $user): string
    {
        return Crypt::decryptString($user->two_factor_secret);
    }

    private function audit(string $action, Request $request, User $user, array $metadata = []): void
    {
        AuditLog::create([
            'user_id'     => $user->id,
            'action'      => $action,
            'entity_type' => User::class,
            'entity_id'   => $user->id,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'metadata'    => $metadata ?: null,
        ]);
    }
}
