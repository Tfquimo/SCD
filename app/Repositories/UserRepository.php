<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        // Uses Eloquent — never raw SQL
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function incrementFailedAttempts(User $user): void
    {
        $user->increment('failed_login_attempts');
    }

    public function resetFailedAttempts(User $user): void
    {
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until'          => null,
        ]);
    }

    public function lockAccount(User $user, DateTimeInterface $until): void
    {
        $user->update(['locked_until' => $until]);
    }

    public function deactivate(User $user): void
    {
        $user->update(['active' => false]);
    }

    public function activate(User $user): void
    {
        $user->update([
            'active'                 => true,
            'failed_login_attempts'  => 0,
            'locked_until'           => null,
        ]);
    }

    public function updateLastLogin(User $user, string $ip): void
    {
        $user->update([
            'last_login_at' => Carbon::now(),
            'last_login_ip' => $ip,
        ]);
    }

    public function all(int $perPage = 20): LengthAwarePaginator
    {
        return User::with('department')
            ->orderBy('name')
            ->paginate($perPage);
    }
}
