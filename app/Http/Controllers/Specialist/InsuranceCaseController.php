<?php

namespace App\Http\Controllers\Specialist;

use App\Http\Controllers\Controller;
use App\Models\InsuranceCase;
use App\Models\Policy;
use App\Models\Client;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InsuranceCaseController extends Controller
{
    // Сторінка створення нового випадку
    public function create()
    {
        $policies = Policy::with(['client', 'policyType'])
            ->where('status', 'active')
            ->get();
            
        return view('specialist.create', compact('policies'));
    }
    
    // Збереження нового випадку
    public function store(Request $request)
    {
        $request->validate([
            'policy_id' => 'required|exists:policies,id',
            'date' => 'required|date|before_or_equal:today',
            'description' => 'required|string|min:10|max:5000',
            'claim_amount' => 'required|numeric|min:0.01',
            'assessed_amount' => 'nullable|numeric|min:0',
        ], [
            'policy_id.required' => 'Виберіть поліс',
            'date.required' => 'Вкажіть дату події',
            'date.before_or_equal' => 'Дата не може бути в майбутньому',
            'description.required' => 'Опишіть подію',
            'description.min' => 'Мінімум 10 символів',
            'claim_amount.required' => 'Вкажіть суму',
            'claim_amount.min' => 'Сума має бути більше 0',
        ]);
        
        $policy = Policy::findOrFail($request->policy_id);
        
        if ($policy->status !== 'active') {
            return back()->with('error', 'Поліс неактивний!');
        }
        
        $insuranceCase = InsuranceCase::create([
            'policy_id' => $request->policy_id,
            'date' => $request->date,
            'description' => $request->description,
            'claim_amount' => $request->claim_amount,
            'assessed_amount' => $request->assessed_amount,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
        
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_insurance_case',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => json_encode([
                'case_id' => $insuranceCase->id,
                'policy_number' => $policy->policy_number,
            ])
        ]);
        
        return redirect()->route('specialist.cases')
            ->with('success', 'Страховий випадок створено!');
    }
    
    // Сторінка редагування
    public function edit($id)
    {
        $case = InsuranceCase::with(['policy.client', 'policy.policyType'])
            ->findOrFail($id);
            
        $policies = Policy::with(['client', 'policyType'])
            ->where('status', 'active')
            ->get();
            
        return view('specialist.edit', compact('case', 'policies'));
    }
    
    // Оновлення випадку
    public function update(Request $request, $id)
    {
        $case = InsuranceCase::findOrFail($id);
        
        $request->validate([
            'policy_id' => 'required|exists:policies,id',
            'date' => 'required|date',
            'description' => 'required|string|min:10',
            'claim_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,in_review,approved,rejected',
        ]);
        
        $oldStatus = $case->status;
        
        $case->update([
            'policy_id' => $request->policy_id,
            'date' => $request->date,
            'description' => $request->description,
            'claim_amount' => $request->claim_amount,
            'assessed_amount' => $request->assessed_amount,
            'status' => $request->status,
            'decision_date' => $request->status == 'approved' && $oldStatus != 'approved' ? now() : $case->decision_date,
        ]);
        
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_insurance_case',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => json_encode([
                'case_id' => $case->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
            ])
        ]);
        
        return redirect()->route('specialist.cases')
            ->with('success', 'Страховий випадок оновлено!');
    }
    
    // Видалення
    public function destroy($id)
    {
        $case = InsuranceCase::findOrFail($id);
        
        if ($case->status == 'approved') {
            return back()->with('error', 'Не можна видалити схвалений випадок!');
        }
        
        $case->delete();
        
        return redirect()->route('specialist.cases')
            ->with('success', 'Страховий випадок видалено!');
    }
    
    // Пошук поліса (AJAX)
    public function searchPolicy(Request $request)
    {
        $search = $request->get('q');
        
        $policies = Policy::with(['client', 'policyType'])
            ->where('status', 'active')
            ->where(function($query) use ($search) {
                $query->where('policy_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function($q) use ($search) {
                        $q->where('last_name', 'like', "%{$search}%")
                          ->orWhere('first_name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                    });
            })
            ->limit(10)
            ->get();
            
        return response()->json($policies);
    }
    
    // Отримання даних поліса
    public function getPolicyData($id)
    {
        $policy = Policy::with(['client', 'policyType'])->findOrFail($id);
        
        return response()->json([
            'policy' => $policy,
            'client' => $policy->client,
            'policy_type' => $policy->policyType,
            'policy_number' => $policy->policy_number,
            'client_name' => ($policy->client->last_name ?? '') . ' ' . ($policy->client->first_name ?? ''),
            'client_phone' => $policy->client->phone ?? '-',
            'client_email' => $policy->client->email ?? '-',
        ]);
    }
}