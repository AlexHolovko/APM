<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // 📊 Головна сторінка менеджера
    public function index()
    {
        return view('manager.dashboard', [
            'clientsCount' => Client::count(),
            'latestClients' => Client::latest()->take(5)->get(),
        ]);
    }

    // ➕ Додавання клієнта
    public function storeClient(Request $request)
{
    $data = $request->validate([
        'last_name' => 'required',
        'first_name' => 'required',
        'middle_name' => 'nullable',

        'birth_date' => 'nullable',
        'tax_number' => 'nullable',

        'country' => 'nullable',
        'region' => 'nullable',
        'city' => 'nullable',
        'street' => 'nullable',
        'house' => 'nullable',
        'apartment' => 'nullable',

        'passport_series' => 'nullable',
        'passport_number' => 'nullable',
        'passport_issued_by' => 'nullable',
        'passport_issued_at' => 'nullable',

        'phone' => 'nullable',
        'email' => 'nullable',
    ]);

    Client::create($data);

    return redirect()->route('manager.dashboard')
        ->with('success', 'Клієнта успішно додано');
}
}