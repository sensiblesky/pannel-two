<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetCode extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'ip_address',
        'fingerprint',
        'resend_count',
        'attempt_count',
        'blocked_until',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'blocked_until' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isBlocked(): bool
    {
        return $this->blocked_until && $this->blocked_until->isFuture();
    }
}
