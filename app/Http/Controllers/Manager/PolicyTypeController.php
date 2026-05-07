<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\PolicyType;
use Illuminate\Http\Request;

class PolicyTypeController extends Controller
{
    public function index()
    {
        $policyTypes = PolicyType::all();
        return view('manager.policy-types.index', compact('policyTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:policy_types',
            'code' => 'required|string|max:50|unique:policy_types',
            'description' => 'nullable|string',
            'default_premium' => 'required|numeric|min:0',
            'duration_months' => 'required|integer|min:1',
            'conditions' => 'nullable|array',
        ]);

        PolicyType::create($validated);

        return back()->with('success', 'Тип полісу створено');
    }

    public function update(Request $request, PolicyType $policyType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:policy_types,name,' . $policyType->id,
            'code' => 'required|string|max:50|unique:policy_types,code,' . $policyType->id,
            'description' => 'nullable|string',
            'default_premium' => 'required|numeric|min:0',
            'duration_months' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $policyType->update($validated);

        return back()->with('success', 'Тип полісу оновлено');
    }

    public function destroy(PolicyType $policyType)
    {
        // Перевірка, чи є прив'язані поліси
        if ($policyType->policies()->count() > 0) {
            return back()->with('error', 'Не можна видалити тип, до якого прив\'язані поліси');
        }
        
        $policyType->delete();
        return back()->with('success', 'Тип полісу видалено');
    }
}