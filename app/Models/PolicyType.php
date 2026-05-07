<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'default_premium',
        'duration_months',
        'conditions',
        'is_active',
    ];

    protected $casts = [
        'default_premium' => 'decimal:2',
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];

    public function policies()
    {
        return $this->hasMany(Policy::class);
    }
}