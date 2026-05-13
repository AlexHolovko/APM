<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Policy;
use App\Models\InsuranceCase;
use App\Models\Client;
use App\Models\PolicyType;
use Carbon\Carbon;

class AccountantFullDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📊 Починаємо створення даних для бухгалтера...');
        
        // 1. Перевіряємо чи є клієнти
        if (Client::count() == 0) {
            $this->createClients();
        }
        
        // 2. Перевіряємо чи є типи полісів
        if (PolicyType::count() == 0) {
            $this->createPolicyTypes();
        }
        
        // 3. Перевіряємо чи є поліси
        if (Policy::count() == 0) {
            $this->createPolicies();
        }
        
        // 4. Перевіряємо чи є страхові випадки
        if (InsuranceCase::count() == 0) {
            $this->createInsuranceCases();
        }
        
        // 5. Створюємо платежі (премії та виплати)
        $this->createPayments();
        
        $this->command->info('✅ Готово! Дані створено успішно.');
        $this->showStats();
    }
    
    private function createClients(): void
    {
        $clients = [
            ['Іваненко', 'Іван', 'Петрович', '1990-01-15', '+380501234567', 'ivan@example.com', 'Київ', 'вул. Хрещатик, 10'],
            ['Петренко', 'Ольга', 'Сергіївна', '1985-05-20', '+380671234568', 'olga@example.com', 'Львів', 'вул. Личаківська, 15'],
            ['Сидоренко', 'Михайло', 'Володимирович', '1982-11-08', '+380931234569', 'mykhailo@example.com', 'Одеса', 'вул. Дерибасівська, 5'],
            ['Коваленко', 'Наталія', 'Андріївна', '1995-03-12', '+380631234560', 'natalia@example.com', 'Харків', 'вул. Сумська, 20'],
            ['Бондаренко', 'Віктор', 'Олексійович', '1988-07-25', '+380731234561', 'viktor@example.com', 'Дніпро', 'пр. Дмитра Яворницького, 30'],
        ];
        
        foreach ($clients as $client) {
            Client::create([
                'last_name' => $client[0],
                'first_name' => $client[1],
                'middle_name' => $client[2],
                'birth_date' => $client[3],
                'phone' => $client[4],
                'email' => $client[5],
                'city' => $client[6],
                'street' => $client[7],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('📋 Створено ' . Client::count() . ' клієнтів');
    }
    
    private function createPolicyTypes(): void
    {
        $types = [
            ['Автострахування', 'auto', 'Страхування транспортних засобів', 5000, 12, 1000],
            ['Медичне страхування', 'health', 'Страхування здоров\'я', 3000, 12, 500],
            ['Страхування життя', 'life', 'Страхування життя', 10000, 12, 0],
            ['Страхування майна', 'property', 'Страхування нерухомості', 4000, 12, 2000],
        ];
        
        foreach ($types as $type) {
            PolicyType::create([
                'name' => $type[0],
                'code' => $type[1],
                'description' => $type[2],
                'default_premium' => $type[3],
                'duration_months' => $type[4],
                'franchise_value' => $type[5],
                'franchise_type' => 'fixed',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('📄 Створено ' . PolicyType::count() . ' типів полісів');
    }
    
    private function createPolicies(): void
    {
        $clients = Client::all();
        $types = PolicyType::all();
        
        $policyNumbers = ['POL-001', 'POL-002', 'POL-003', 'POL-004', 'POL-005', 'POL-006', 'POL-007', 'POL-008'];
        
        foreach ($clients as $index => $client) {
            $type = $types[$index % count($types)];
            Policy::create([
                'policy_number' => $policyNumbers[$index] ?? 'POL-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'client_id' => $client->id,
                'policy_type_id' => $type->id,
                'start_date' => Carbon::create(2025, 1, 1),
                'end_date' => Carbon::create(2025, 12, 31),
                'premium' => $type->default_premium,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('📑 Створено ' . Policy::count() . ' полісів');
    }
    
    private function createInsuranceCases(): void
    {
        $policies = Policy::all();
        
        $descriptions = [
            'ДТП на перехресті, пошкоджено передній бампер та фару',
            'Пошкодження майна внаслідок пожежі',
            'Крадіжка особистого майна',
            'Затоплення квартири сусідами',
            'Травма внаслідок нещасного випадку'
        ];
        
        foreach ($policies as $index => $policy) {
            $statuses = ['approved', 'approved', 'pending', 'rejected', 'approved'];
            $status = $statuses[$index % count($statuses)];
            $claimAmount = rand(5000, 50000);
            
            InsuranceCase::create([
                'policy_id' => $policy->id,
                'date' => Carbon::now()->subDays(rand(10, 90)),
                'description' => $descriptions[array_rand($descriptions)],
                'claim_amount' => $claimAmount,
                'status' => $status,
                'payment_status' => $status == 'approved' ? 'pending' : 'rejected',
                'approved_amount' => $status == 'approved' ? $claimAmount * 0.8 : null,
                'decision_date' => $status != 'pending' ? Carbon::now()->subDays(rand(1, 30)) : null,
                'decision_notes' => $status != 'pending' ? 'Рішення прийнято на підставі наданих документів' : null,
                'created_at' => Carbon::now()->subMonths(rand(1, 6)),
                'updated_at' => Carbon::now(),
            ]);
        }
        
        $this->command->info('⚠️ Створено ' . InsuranceCase::count() . ' страхових випадків');
    }
    
    private function createPayments(): void
    {
        $policies = Policy::all();
        
        // Премії за 2025 рік (по місяцях)
        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        
        foreach ($policies as $policy) {
            foreach ($months as $month) {
                $paymentDate = Carbon::create(2025, $month, 15);
                $status = $month <= 5 ? 'completed' : 'pending';
                
                Payment::create([
                    'policy_id' => $policy->id,
                    'amount' => $policy->premium / 12,
                    'payment_type' => 'premium',
                    'date' => $paymentDate,
                    'status' => $status,
                    'transaction_id' => $status == 'completed' ? 'TXN_' . strtoupper(uniqid()) : null,
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate,
                ]);
            }
        }
        
        // Виплати за схваленими випадками
        $approvedCases = InsuranceCase::where('status', 'approved')->whereNotNull('approved_amount')->get();
        
        foreach ($approvedCases as $case) {
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
        
        $this->command->info('💰 Створено платежів:');
        $this->command->info('   - Премії: ' . Payment::where('payment_type', 'premium')->count());
        $this->command->info('   - Виплати: ' . Payment::where('payment_type', 'payout')->count());
    }
    
    private function showStats(): void
    {
        $totalPremiums = Payment::where('payment_type', 'premium')->sum('amount');
        $totalPayouts = Payment::where('payment_type', 'payout')->sum('amount');
        $pendingPayouts = Payment::where('payment_type', 'payout')->where('status', 'pending')->sum('amount');
        
        $this->command->info('');
        $this->command->info('📊 ПІДСУМКОВА СТАТИСТИКА:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('💰 Загальна сума премій:   ' . number_format($totalPremiums, 2) . ' грн');
        $this->command->info('💸 Загальна сума виплат:   ' . number_format($totalPayouts, 2) . ' грн');
        $this->command->info('📈 Прибуток:               ' . number_format($totalPremiums - $totalPayouts, 2) . ' грн');
        $this->command->info('⏳ Очікує виплати:         ' . number_format($pendingPayouts, 2) . ' грн');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}