<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\PolicyType;
use Illuminate\Http\Request;

class PolicyTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $policyTypes = PolicyType::latest()->paginate(15);
        
        return view('manager.policy-types.index', compact('policyTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:policy_types,name',
            'code' => 'required|string|max:50|unique:policy_types,code',
            'description' => 'nullable|string',
            'default_premium' => 'required|numeric|min:0',
            'franchise_value' => 'nullable|numeric|min:0',
            'franchise_type' => 'nullable|in:fixed,percentage',
            'duration_months' => 'required|integer|min:1|max:120',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : true; // за замовчуванням активний
        
        PolicyType::create($validated);

        return redirect()->route('manager.policy-types.index')
            ->with('success', 'Тип полісу успішно додано');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PolicyType $policyType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:policy_types,name,' . $policyType->id,
            'code' => 'required|string|max:50|unique:policy_types,code,' . $policyType->id,
            'description' => 'nullable|string',
            'default_premium' => 'required|numeric|min:0',
            'franchise_value' => 'nullable|numeric|min:0',
            'franchise_type' => 'nullable|in:fixed,percentage',
            'duration_months' => 'required|integer|min:1|max:120',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool)$request->is_active : false;
        
        $policyType->update($validated);

        return redirect()->route('manager.policy-types.index')
            ->with('success', 'Тип полісу успішно оновлено');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PolicyType $policyType)
    {
        // Перевірка, чи є поліси цього типу
        if ($policyType->policies()->count() > 0) {
            return back()->with('error', 'Не можна видалити тип полісу, оскільки існують поліси цього типу.');
        }

        $policyType->delete();

        return back()->with('success', 'Тип полісу успішно видалено');
    }
}