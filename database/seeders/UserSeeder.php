<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run()
  {
    // створення користувача с admin ролью
    $user = User::firstOrCreate(
      ['email' => 'admin@example.com'],
      [
        'name' => 'Admin',
        'password' => bcrypt('password'),
      ]
    );
    $user->assignRole('admin');
    // створення користувача с manager ролью
    $manager = User::firstOrCreate(['email' => 'manager@example.com'], [
      'name' => 'Manager',
      'password' => bcrypt('password'),
    ]);
    $manager->assignRole('manager');
  }
}
