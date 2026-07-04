<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_roles_are_correctly_identified(): void
    {
        $admin = User::factory()->make(['role' => 'admin']);
        $manager = User::factory()->make(['role' => 'manager']);
        $employee = User::factory()->make(['role' => 'employee']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isManager());

        $this->assertTrue($manager->isManager());
        $this->assertTrue($employee->isEmployee());
    }

    public function test_user_active_state(): void
    {
        $activeUser = User::factory()->make(['active' => true]);
        $inactiveUser = User::factory()->make(['active' => false]);

        $this->assertTrue($activeUser->isActive());
        $this->assertFalse($inactiveUser->isActive());
    }

    public function test_user_locked_state(): void
    {
        $unlockedUser = User::factory()->make(['locked_until' => null]);
        $lockedUser = User::factory()->make(['locked_until' => now()->addMinutes(30)]);
        $expiredLockUser = User::factory()->make(['locked_until' => now()->subMinutes(10)]);

        $this->assertFalse($unlockedUser->isLocked());
        $this->assertTrue($lockedUser->isLocked());
        $this->assertFalse($expiredLockUser->isLocked());
    }
}
