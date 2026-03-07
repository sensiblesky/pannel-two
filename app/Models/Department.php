<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'branch_id',
        'status',
        'created_by',
        'deleted_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (Department $department) {
            if (empty($department->uuid)) {
                $department->uuid = Str::uuid()->toString();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
