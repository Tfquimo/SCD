<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status');
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/reset-password/fake-token?email=test@example.com');

        $response->assertStatus(200);
    }

    public function test_password_can_be_reset_with_strong_password(): void
    {
        $user = User::factory()->create();

        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewStrongP@ssw0rd!',
            'password_confirmation' => 'NewStrongP@ssw0rd!',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/login');
    }

    public function test_password_cannot_be_reset_with_weak_password(): void
    {
        $user = User::factory()->create();

        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'weakpass',
            'password_confirmation' => 'weakpass',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
