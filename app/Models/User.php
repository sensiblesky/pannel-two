<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'role',
        'status',
        'branch_id',
        'avatar',
        'two_factor_enabled',
        'two_factor_method',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'is_suspended',
        'created_by',
        'deleted_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->uid)) {
                $user->uid = Str::uuid()->toString();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uid';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function suspensions()
    {
        return $this->hasMany(UserSuspension::class)->latest('suspended_at');
    }

    public function activeSuspension()
    {
        return $this->hasOne(UserSuspension::class)->whereNull('unsuspended_at')->latest('suspended_at');
    }
}
