<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // 📊 Головна сторінка менеджера
    public function index()
    {
        // Отримуємо всіх клієнтів (включно з доданими через SQL)
        $latestClients = Client::orderBy('id', 'desc')->take(10)->get();
        
        // Для кожного клієнта, у якого немає users, створюємо (якщо є email)
        foreach ($latestClients as $client) {
            if ($client->email && !User::where('email', $client->email)->exists()) {
                User::updateOrCreate(
                    ['email' => $client->email],
                    [
                        'name' => $client->last_name . ' ' . $client->first_name,
                        'password' => Hash::make('password123'),
                        'role' => 'client',
                    ]
                );
            }
        }

        return view('manager.dashboard', [
            'clientsCount' => Client::count(),
            'latestClients' => $latestClients,
            'newClientsThisMonth' => Client::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'activePoliciesCount' => \App\Models\Policy::where('status', 'active')->count(),
            'payoutsThisMonth' => \App\Models\Payment::where('payment_type', 'payout')
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->sum('amount'),
        ]);
    }

    // ➕ Додавання клієнта
    public function storeClient(Request $request)
    {
        $data = $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',

            'birth_date' => 'nullable|date',
            'tax_number' => 'nullable|string|max:50',

            'country' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'street' => 'nullable|string|max:255',
            'house' => 'nullable|string|max:50',
            'apartment' => 'nullable|string|max:50',

            'passport_series' => 'nullable|string|max:20',
            'passport_number' => 'nullable|string|max:50',
            'passport_issued_by' => 'nullable|string|max:255',
            'passport_issued_at' => 'nullable|date',

            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
        ]);

        try {
            DB::beginTransaction();

            // Створюємо клієнта
            $client = Client::create($data);

            // Якщо є email - створюємо або оновлюємо користувача
            if (!empty($data['email'])) {
                User::updateOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['last_name'] . ' ' . $data['first_name'],
                        'password' => Hash::make('password123'),
                        'role' => 'client',
                    ]
                );
            }

            DB::commit();

            return redirect()->route('manager.dashboard')
                ->with('success', 'Клієнта успішно додано');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('manager.dashboard')
                ->with('error', 'Помилка при додаванні клієнта: ' . $e->getMessage());
        }
    }
}