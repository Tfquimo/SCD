<?php

namespace Tests\Unit\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Auth\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;
    private $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userRepository = app(UserRepositoryInterface::class);
        $this->authService = new AuthService($this->userRepository);
    }

    public function test_attempt_with_valid_credentials_returns_user(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $request = Request::create('/login', 'POST', [
            'email' => $user->email,
            'password' => 'password123',
        ]);
        $request->setLaravelSession(app('session')->driver());

        $authenticatedUser = $this->authService->attempt($request);

        $this->assertEquals($user->id, $authenticatedUser->id);
    }

    public function test_attempt_with_invalid_credentials_throws_validation_exception(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $request = Request::create('/login', 'POST', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
        
        $this->expectException(ValidationException::class);
        
        $this->authService->attempt($request);
    }
}
