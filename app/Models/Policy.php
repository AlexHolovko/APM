<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;

    public $timestamps = false; // Если нет created_at/updated_at

    protected $fillable = [
        'client_id',
        'policy_type_id',
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
    
    // Закомментируйте или удалите, если нет таблицы applications
    // public function application()
    // {
    //     return $this->belongsTo(Application::class);
    // }
}