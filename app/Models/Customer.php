<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'avatar',
        'company',
        'source',
        'external_id',
        'visitor_id',
        'country',
        'city',
        'ip_address',
        'user_agent',
        'last_seen_at',
        'last_message_at',
        'status',
        'notes',
        'branch_id',
        'created_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
            'last_message_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            if (empty($customer->uuid)) {
                $customer->uuid = Str::uuid()->toString();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getFullNameAttribute(): string
    {
        return trim(collect([$this->first_name, $this->middle_name, $this->last_name])->filter()->implode(' '));
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
