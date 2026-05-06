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

    public function destroy(Client $client)
    {
        $client->delete();

        return back()->with('success', 'Клієнта видалено');
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
}