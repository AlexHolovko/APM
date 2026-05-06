<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;

    protected $table = 'policies';
    
    protected $fillable = [
        'policy_number',
        'start_date',
        'end_date',
        'premium',
        'status',
        'user_id',
        'application_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'premium' => 'decimal:2'
    ];

    // Статусы полисов
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PENDING = 'pending';

    const STATUSES = [
        self::STATUS_ACTIVE => 'Активный',
        self::STATUS_EXPIRED => 'Просрочен',
        self::STATUS_CANCELLED => 'Отменен',
        self::STATUS_PENDING => 'Ожидает'
    ];

    // Отношения
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    // Скоупы
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Геттеры
    public function getStatusNameAttribute()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getDaysRemainingAttribute()
    {
        if ($this->end_date < now()) {
            return 0;
        }
        return now()->diffInDays($this->end_date);
    }

    public function isValid()
    {
        return $this->status === self::STATUS_ACTIVE && $this->end_date >= now();
    }
}