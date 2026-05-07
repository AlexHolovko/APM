<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use App\Models\Client;      // Додаємо модель Client
use App\Models\PolicyType;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function index()
    {
        return view('manager.policies.index', [
            'policies' => Policy::with(['client', 'policyType'])->latest('id')->get(),
            'clients' => Client::all(), // Отримуємо всіх клієнтів з таблиці clients
            'policyTypes' => PolicyType::where('is_active', true)->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',  // Валідація на таблицю clients
            'policy_type_id' => 'required|exists:policy_types,id',
            'policy_number' => 'required|string|max:255|unique:policies',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'premium' => 'required|numeric|min:0',
        ]);

        $validated['status'] = Policy::STATUS_ACTIVE;

        Policy::create($validated);

        return back()->with('success', 'Поліс створено');
    }

    public function update(Request $request, Policy $policy)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'policy_type_id' => 'required|exists:policy_types,id',
            'policy_number' => 'required|string|max:255|unique:policies,policy_number,' . $policy->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'premium' => 'required|numeric|min:0',
            'status' => 'required|in:active,expired,cancelled',
        ]);

        $policy->update($validated);

        return back()->with('success', 'Поліс оновлено');
    }

    public function destroy(Policy $policy)
    {
        $policy->delete();

        return back()->with('success', 'Поліс видалено');
    }
}