<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    // ─── Fillable ────────────────────────────────────────────────────
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'active',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
        'last_login_ip',
    ];

    // ─── Hidden from serialisation ───────────────────────────────────
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    // ─── Casts ───────────────────────────────────────────────────────
    protected function casts(): array
    {
        return [
            'email_verified_at'        => 'datetime',
            'two_factor_confirmed_at'  => 'datetime',
            'locked_until'             => 'datetime',
            'last_login_at'            => 'datetime',
            'active'                   => 'boolean',
            'failed_login_attempts'    => 'integer',
            'password'                 => 'hashed',
            // two_factor_secret stored encrypted at application layer
        ];
    }

    // ─── Role helpers ────────────────────────────────────────────────
    public function isAdmin(): bool
    {
        return strtolower($this->role ?? '') === 'admin' || $this->hasRole('admin');
    }

    public function isManager(): bool
    {
        return strtolower($this->role ?? '') === 'manager' || strtolower($this->role ?? '') === 'gestor' || $this->hasRole('manager');
    }

    public function isEmployee(): bool
    {
        return strtolower($this->role ?? '') === 'employee' || strtolower($this->role ?? '') === 'funcionário' || $this->hasRole('employee');
    }

    // ─── Account state ───────────────────────────────────────────────
    public function isActive(): bool
    {
        return $this->active === true;
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function hasVerified2FA(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    // ─── Relationships ───────────────────────────────────────────────
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // ─── Scopes ─────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
