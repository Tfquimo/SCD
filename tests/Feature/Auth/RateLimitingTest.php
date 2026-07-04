<?php

namespace Tests\Feature\Auth;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // Attempt 5 times
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
            $response->assertSessionHasErrors();
        }

        // 6th attempt should be blocked by RateLimiter
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);

        $user->refresh();
        $this->assertEquals(5, $user->failed_login_attempts);
        $this->assertTrue($user->isLocked());
    }

    public function test_account_locks_after_five_failed_attempts(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'failed_login_attempts' => 4,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $user->refresh();
        
        $this->assertEquals(5, $user->failed_login_attempts);
        $this->assertNotNull($user->locked_until);
        
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => AuditLog::ACTION_ACCOUNT_LOCKED,
        ]);
    }
}
