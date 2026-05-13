<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Policy;
use App\Models\InsuranceCase;
use Carbon\Carbon;

class AccountantTestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Отримуємо існуючі поліси
        $policies = Policy::where('status', 'active')->get();
        
        if ($policies->isEmpty()) {
            $this->command->info('Немає активних полісів. Спочатку створіть поліси!');
            return;
        }
        
        // Перевіряємо чи є вже платежі
        if (Payment::count() > 0) {
            $this->command->info('Платежі вже існують. Пропускаємо...');
            return;
        }
        
        // Створюємо платежі премій за різні місяці
        $paymentDates = [
            '2026-01-15', '2026-02-15', '2026-03-15', 
            '2026-04-15', '2026-05-15', '2026-06-15'
        ];
        
        foreach ($policies as $policy) {
            foreach ($paymentDates as $date) {
                Payment::create([
                    'policy_id' => $policy->id,
                    'amount' => $policy->premium / 12, // щомісячний платіж
                    'payment_type' => 'premium',
                    'date' => $date,
                    'status' => 'completed',
                    'transaction_id' => 'TXN_' . strtoupper(uniqid()),
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }
        
        // Створюємо тестові виплати (payouts)
        $insuranceCases = InsuranceCase::where('status', 'approved')->whereNotNull('approved_amount')->get();
        
        if ($insuranceCases->isNotEmpty()) {
            foreach ($insuranceCases as $case) {
                Payment::create([
                    'policy_id' => $case->policy_id,
                    'insurance_case_id' => $case->id,
                    'amount' => $case->approved_amount,
                    'payment_type' => 'payout',
                    'date' => $case->decision_date ?? Carbon::now(),
                    'status' => 'completed',
                    'transaction_id' => 'PYT_' . strtoupper(uniqid()),
                    'created_at' => $case->decision_date ?? Carbon::now(),
                    'updated_at' => $case->decision_date ?? Carbon::now(),
                ]);
            }
        } else {
            // Якщо немає страхових випадків, створюємо тестові виплати
            foreach ($policies->take(3) as $policy) {
                Payment::create([
                    'policy_id' => $policy->id,
                    'amount' => rand(5000, 50000),
                    'payment_type' => 'payout',
                    'date' => Carbon::now()->subDays(rand(1, 60)),
                    'status' => 'completed',
                    'transaction_id' => 'PYT_' . strtoupper(uniqid()),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
        
        $this->command->info('✅ Створено платежів: ' . Payment::count());
        $this->command->info('   📌 Премії (premium): ' . Payment::where('payment_type', 'premium')->count());
        $this->command->info('   💸 Виплати (payout): ' . Payment::where('payment_type', 'payout')->count());
        $this->command->info('   💰 Загальна сума премій: ' . number_format(Payment::where('payment_type', 'premium')->sum('amount'), 2) . ' грн');
        $this->command->info('   💵 Загальна сума виплат: ' . number_format(Payment::where('payment_type', 'payout')->sum('amount'), 2) . ' грн');
    }
}