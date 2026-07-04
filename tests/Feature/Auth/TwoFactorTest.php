<?php

namespace Tests\Feature\Auth;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FALaravel\Google2FA;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_factor_challenge_screen_can_be_rendered(): void
    {
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response = $this->get('/two-factor-challenge');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_with_valid_two_factor_code(): void
    {
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();
        
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $validCode = $google2fa->getCurrentOtp($secret);

        $response = $this->post('/two-factor-challenge', [
            'code' => $validCode,
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertTrue(session('auth.2fa_verified'));
        
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => AuditLog::ACTION_2FA_VERIFIED,
        ]);
    }

    public function test_users_cannot_authenticate_with_invalid_two_factor_code(): void
    {
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response = $this->post('/two-factor-challenge', [
            'code' => '000000',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertFalse(session('auth.2fa_verified', false));
        
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => AuditLog::ACTION_2FA_FAILED,
        ]);
    }
}
