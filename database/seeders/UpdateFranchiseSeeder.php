<?php

namespace Database\Seeders;

use App\Models\PolicyType;
use Illuminate\Database\Seeder;

class UpdateFranchiseSeeder extends Seeder
{
    public function run()
    {
        // Оновлюємо існуючі типи полісів з франшизою
        PolicyType::where('code', 'auto')->update([
            'franchise_value' => 1000,
            'franchise_type' => 'fixed'
        ]);
        
        PolicyType::where('code', 'health')->update([
            'franchise_value' => 5,
            'franchise_type' => 'percentage'
        ]);
        
        PolicyType::where('code', 'life')->update([
            'franchise_value' => 0,
            'franchise_type' => 'fixed'
        ]);
        
        PolicyType::where('code', 'travel')->update([
            'franchise_value' => 20,
            'franchise_type' => 'percentage'
        ]);
    }
}