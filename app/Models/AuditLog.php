<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    // Audit logs are immutable — no updated_at
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    // ─── Actions constants ───────────────────────────────────────────
    public const ACTION_LOGIN              = 'login';
    public const ACTION_LOGOUT             = 'logout';
    public const ACTION_LOGIN_FAILED       = 'login_failed';
    public const ACTION_ACCOUNT_LOCKED     = 'account_locked';
    public const ACTION_2FA_VERIFIED       = '2fa_verified';
    public const ACTION_2FA_FAILED         = '2fa_failed';
    public const ACTION_2FA_ENABLED        = '2fa_enabled';
    public const ACTION_2FA_DISABLED       = '2fa_disabled';
    public const ACTION_PASSWORD_RESET     = 'password_reset';
    public const ACTION_ACCOUNT_DEACTIVATED = 'account_deactivated';
    public const ACTION_ACCOUNT_ACTIVATED  = 'account_activated';
    public const ACTION_PERMISSION_CHANGED = 'permission_changed';
    public const ACTION_ACCESS_DENIED      = 'access_denied';

    // ─── Relationships ───────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ─────────────────────────────────────────────────────
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeInDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
