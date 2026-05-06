<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;
class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        $clients = [
            [
                'last_name' => 'Коваленко',
                'first_name' => 'Андрій',
                'middle_name' => 'Ігорович',
                'birth_date' => '1995-04-12',
                'tax_number' => '1234567890',
                'country' => 'Україна',
                'region' => 'Вінницька',
                'city' => 'Вінниця',
                'street' => 'Шевченка',
                'house' => '12',
                'apartment' => '5',
                'passport_series' => 'AB',
                'passport_number' => '123456',
                'passport_issued_by' => 'МВС України',
                'passport_issued_at' => '2015-06-10',
                'phone' => '+380501112233',
                'email' => 'andriy@example.com',
            ],
            [
                'last_name' => 'Мельник',
                'first_name' => 'Ольга',
                'middle_name' => 'Сергіївна',
                'birth_date' => '1998-09-21',
                'tax_number' => '2234567890',
                'country' => 'Україна',
                'region' => 'Київська',
                'city' => 'Київ',
                'street' => 'Лесі Українки',
                'house' => '8',
                'apartment' => '14',
                'passport_series' => 'CD',
                'passport_number' => '654321',
                'passport_issued_by' => 'МВС України',
                'passport_issued_at' => '2018-02-15',
                'phone' => '+380671234567',
                'email' => 'olga@example.com',
            ],
            [
                'last_name' => 'Іваненко',
                'first_name' => 'Петро',
                'middle_name' => 'Вікторович',
                'birth_date' => '1990-01-10',
                'tax_number' => '3234567890',
                'country' => 'Україна',
                'region' => 'Львівська',
                'city' => 'Львів',
                'street' => 'Городоцька',
                'house' => '45',
                'apartment' => '2',
                'passport_series' => 'EF',
                'passport_number' => '111222',
                'passport_issued_by' => 'МВС України',
                'passport_issued_at' => '2012-05-20',
                'phone' => '+380931112233',
                'email' => 'petro@example.com',
            ],
        ];
    }
}
