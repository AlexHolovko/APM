<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceCase extends Model
{
    use HasFactory;

    // Если таблица называется 'insurance_cases'
    protected $table = 'insurance_cases';

    protected $fillable = [
        'policy_id',
        'incident_date',
        'description',
        'claim_amount',
        'status',
        'decision_date',
        'decision_notes',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'decision_date' => 'date',
        'claim_amount' => 'decimal:2',
    ];

    public function policy()
    {
        return $this->belongsTo(Policy::class);
    }
}