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
        'franchise_value',
        'franchise_type',
        'duration_months',
        'conditions',
        'is_active',
    ];

    protected $casts = [
        'default_premium' => 'decimal:2',
        'franchise_value' => 'decimal:2',
        'duration_months' => 'integer',
        'is_active' => 'boolean',
    ];

    public function policies()
    {
        return $this->hasMany(Policy::class);
    }
}