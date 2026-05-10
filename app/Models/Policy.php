<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;

    // Отключаем автоматические timestamps
    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'policy_type_id',
        'application_id',
        'policy_number',
        'start_date',
        'end_date',
        'premium',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'premium' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function policyType()
    {
        return $this->belongsTo(PolicyType::class);
    }
    
    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}