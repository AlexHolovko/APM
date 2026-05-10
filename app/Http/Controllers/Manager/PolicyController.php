<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Policy;
use App\Models\PolicyType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function index()
    {
        // Убираем latest() так как нет created_at
        // Сортируем по id или по start_date
        $policies = Policy::with(['client', 'policyType'])
            ->orderBy('id', 'desc') // или ->orderBy('start_date', 'desc')
            ->paginate(10);
        
        $clients = Client::orderBy('last_name')->get();
        $policyTypes = PolicyType::where('is_active', true)->get();

        return view('manager.policies.index', compact('policies', 'clients', 'policyTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'policy_type_id' => 'required|exists:policy_types,id',
            'policy_number' => 'required|string|unique:policies',
            'start_date' => 'required|date',
        ]);

        // Получаем тип полиса
        $policyType = PolicyType::findOrFail($data['policy_type_id']);
        
        // Рассчитываем дату окончания
        $startDate = Carbon::parse($data['start_date']);
        $endDate = $startDate->copy()->addMonths($policyType->duration_months);
        
        // Создаем полис
        Policy::create([
            'client_id' => $data['client_id'],
            'policy_type_id' => $data['policy_type_id'],
            'policy_number' => $data['policy_number'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'premium' => $policyType->default_premium,
            'status' => 'active',
        ]);

        return redirect()->route('manager.policies.index')
            ->with('success', 'Поліс успішно додано');
    }

    public function update(Request $request, Policy $policy)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'policy_type_id' => 'required|exists:policy_types,id',
            'policy_number' => 'required|string|unique:policies,policy_number,' . $policy->id,
            'start_date' => 'required|date',
            'status' => 'required|in:active,expired,cancelled',
        ]);

        // Получаем тип полиса
        $policyType = PolicyType::findOrFail($data['policy_type_id']);
        
        // Рассчитываем дату окончания
        $startDate = Carbon::parse($data['start_date']);
        $endDate = $startDate->copy()->addMonths($policyType->duration_months);
        
        // Обновляем полис
        $policy->update([
            'client_id' => $data['client_id'],
            'policy_type_id' => $data['policy_type_id'],
            'policy_number' => $data['policy_number'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'premium' => $policyType->default_premium,
            'status' => $data['status'],
        ]);

        return redirect()->route('manager.policies.index')
            ->with('success', 'Поліс успішно оновлено');
    }

    public function destroy(Policy $policy)
    {
        $policy->delete();

        return redirect()->route('manager.policies.index')
            ->with('success', 'Поліс видалено');
    }
}