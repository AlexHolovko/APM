<?php

namespace Database\Seeders;

use App\Models\PolicyType;
use Illuminate\Database\Seeder;

class PolicyTypeSeeder extends Seeder
{
    public function run()
    {
        $policyTypes = [
            [
                'name' => 'Автострахування',
                'code' => 'auto',
                'description' => 'Страхування транспортних засобів',
                'default_premium' => 5000,
                'duration_months' => 12,
                'is_active' => true,
            ],
            [
                'name' => 'Медичне страхування',
                'code' => 'health',
                'description' => 'Страхування здоров\'я та медичних послуг',
                'default_premium' => 3000,
                'duration_months' => 12,
                'is_active' => true,
            ],
            [
                'name' => 'Страхування життя',
                'code' => 'life',
                'description' => 'Страхування життя та здоров\'я',
                'default_premium' => 10000,
                'duration_months' => 12,
                'is_active' => true,
            ],
            [
                'name' => 'Страхування подорожей',
                'code' => 'travel',
                'description' => 'Страхування туристів та подорожуючих',
                'default_premium' => 1000,
                'duration_months' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Страхування майна',
                'code' => 'property',
                'description' => 'Страхування нерухомості та майна',
                'default_premium' => 2000,
                'duration_months' => 12,
                'is_active' => true,
            ],
            [
                'name' => 'Страхування відповідальності',
                'code' => 'liability',
                'description' => 'Страхування цивільно-правової відповідальності',
                'default_premium' => 1500,
                'duration_months' => 12,
                'is_active' => true,
            ],
        ];

        foreach ($policyTypes as $type) {
            PolicyType::updateOrCreate(
                ['code' => $type['code']], // Перевіряємо за кодом
                $type // Оновлюємо або створюємо
            );
        }

        $this->command->info('Типи полісів успішно додані!');
    }
}