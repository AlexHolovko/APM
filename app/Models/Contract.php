<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $table = 'contracts';
    
    protected $fillable = [
        'contract_number',
        'signed_date',
        'payment_schedule',
        'policy_id'
    ];

    protected $casts = [
        'signed_date' => 'date',
        'payment_schedule' => 'array'
    ];

    // Отношения
    public function policy()
    {
        return $this->belongsTo(Policy::class);
    }

    // Геттер для следующего платежа
    public function getNextPaymentAttribute()
    {
        if (!$this->payment_schedule || empty($this->payment_schedule)) {
            return null;
        }

        $now = now();
        foreach ($this->payment_schedule as $payment) {
            if (strtotime($payment['due_date']) >= $now && !($payment['paid'] ?? false)) {
                return (object) $payment;
            }
        }
        return null;
    }

    // Статус оплаты
    public function getPaymentStatusAttribute()
    {
        if (!$this->payment_schedule || empty($this->payment_schedule)) {
            return 'no_schedule';
        }

        $total = count($this->payment_schedule);
        $paid = collect($this->payment_schedule)->where('paid', true)->count();

        if ($paid == 0) return 'not_paid';
        if ($paid == $total) return 'fully_paid';
        return 'partially_paid';
    }

    public function getPaymentStatusNameAttribute()
    {
        return [
            'not_paid' => 'Не оплачен',
            'partially_paid' => 'Частично оплачен',
            'fully_paid' => 'Полностью оплачен',
            'no_schedule' => 'Нет графика'
        ][$this->payment_status] ?? 'Неизвестно';
    }
}