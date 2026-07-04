<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Services\Auth\AuthService;
use App\Services\Auth\TwoFactorService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Policy map — Eloquent model → Policy class.
     */
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    public function register(): void
    {
        // Bind interface to implementation — swap in tests via fake
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        // Singleton services — one instance per request lifecycle
        $this->app->singleton(AuthService::class);
        $this->app->singleton(TwoFactorService::class);
    }

    public function boot(): void
    {
        $this->registerPolicies();

        // ─── Gates ──────────────────────────────────────────────────
        Gate::define('admin-only', fn(User $user) => $user->isAdmin());

        Gate::define('admin-or-manager', fn(User $user) => $user->isAdmin() || $user->isManager());

        Gate::define('view-audit-logs', fn(User $user) => $user->isAdmin());

        Gate::define('manage-users', fn(User $user) => $user->isAdmin());

        Gate::define('manage-department', function (User $user, \App\Models\Department $department) {
            return $user->isAdmin() || $user->id === $department->manager_id;
        });
    }
}
