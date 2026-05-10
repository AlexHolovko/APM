<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query();

        // 🔍 Фильтр по имени
        if ($request->name) {
            $query->where('last_name', 'like', "%{$request->name}%")
                  ->orWhere('first_name', 'like', "%{$request->name}%");
        }

        // 🔍 Фильтр по телефону
        if ($request->phone) {
            $query->where('phone', 'like', "%{$request->phone}%");
        }

        $clients = $query->latest()->paginate(10);

        return view('manager.clients.index', compact('clients'));
    }

    /**
     * Показати форму створення нового клієнта
     */
    public function create()
    {
        return view('manager.clients.create');
    }

    /**
     * Зберегти нового клієнта
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',

            'birth_date' => 'nullable|date',
            'tax_number' => 'nullable|string|max:20',

            'country' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'street' => 'nullable|string|max:255',
            'house' => 'nullable|string|max:20',
            'apartment' => 'nullable|string|max:20',

            'passport_series' => 'nullable|string|max:10',
            'passport_number' => 'nullable|string|max:20',
            'passport_issued_by' => 'nullable|string|max:255',
            'passport_issued_at' => 'nullable|date',

            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $client = Client::create($data);

        return redirect()->route('manager.clients.index')
            ->with('success', 'Клієнта успішно додано');
    }

    /**
     * Показати форму редагування клієнта
     */
    public function edit(Client $client)
    {
        return view('manager.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
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

        $client->update($data);

        return redirect()->route('manager.clients.index')
            ->with('success', 'Клієнта оновлено');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return back()->with('success', 'Клієнта видалено');
    }
}