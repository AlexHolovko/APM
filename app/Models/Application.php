<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $table = 'applications';

    protected $fillable = [
        'client_id',
        'policy_type_id',
        'status',
        'application_date',
        'notes',
    ];

    protected $casts = [
        'application_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function policyType()
    {
        return $this->belongsTo(PolicyType::class);
    }
}