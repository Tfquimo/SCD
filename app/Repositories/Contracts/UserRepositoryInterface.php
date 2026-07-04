<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function create(array $data): User;

    public function update(User $user, array $data): bool;

    public function incrementFailedAttempts(User $user): void;

    public function resetFailedAttempts(User $user): void;

    public function lockAccount(User $user, \DateTimeInterface $until): void;

    public function deactivate(User $user): void;

    public function activate(User $user): void;

    public function updateLastLogin(User $user, string $ip): void;

    public function all(int $perPage = 20): LengthAwarePaginator;
}
