<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSuspension extends Model
{
    protected $fillable = [
        'user_id',
        'reason',
        'suspended_by',
        'unsuspended_by',
        'suspended_at',
        'unsuspended_at',
    ];

    protected function casts(): array
    {
        return [
            'suspended_at' => 'datetime',
            'unsuspended_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function suspendedByUser()
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function unsuspendedByUser()
    {
        return $this->belongsTo(User::class, 'unsuspended_by');
    }

    public function isActive(): bool
    {
        return is_null($this->unsuspended_at);
    }
}
