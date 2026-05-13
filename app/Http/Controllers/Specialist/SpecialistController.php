<?php

namespace App\Http\Controllers\Specialist;

use App\Http\Controllers\Controller;
use App\Models\InsuranceCase;
use App\Models\Policy;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SpecialistController extends Controller
{
    // Головна панель
    public function dashboard()
    {
        $stats = [
            'total' => InsuranceCase::count(),
            'pending' => InsuranceCase::where('status', 'pending')->count(),
            'in_review' => InsuranceCase::where('status', 'in_review')->count(),
            'approved' => InsuranceCase::where('status', 'approved')->count(),
            'rejected' => InsuranceCase::where('status', 'rejected')->count(),
            'total_payouts' => InsuranceCase::where('status', 'approved')->sum('approved_amount') ?? 0,
        ];
        
        $recentCases = InsuranceCase::with(['policy.client'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('specialist.dashboard', compact('stats', 'recentCases'));
    }
    
    // Список всіх випадків
    public function cases(Request $request)
    {
        $query = InsuranceCase::with(['policy.client']);
        
        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        $cases = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('specialist.cases', compact('cases'));
    }
    
    // Форма створення
    public function create()
    {
        $policies = Policy::with(['client'])->where('status', 'active')->get();
        return view('specialist.create', compact('policies'));
    }
    
    // Збереження нового випадку
    public function store(Request $request)
    {
        $request->validate([
            'policy_id' => 'required|exists:policies,id',
            'date' => 'required|date',
            'description' => 'required|string|min:10',
            'claim_amount' => 'required|numeric|min:0',
        ]);
        
        $case = InsuranceCase::create([
            'policy_id' => $request->policy_id,
            'date' => $request->date,
            'description' => $request->description,
            'claim_amount' => $request->claim_amount,
            'assessed_amount' => $request->assessed_amount,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
        
        return redirect()->route('specialist.cases')
            ->with('success', 'Страховий випадок створено!');
    }
    
    // Перегляд одного випадку
    public function show($id)
    {
        $case = InsuranceCase::with(['policy.client', 'policy.policyType'])->findOrFail($id);
        return view('specialist.show', compact('case'));
    }
    
    // Сторінка розгляду
    public function review($id)
    {
        $case = InsuranceCase::with(['policy.client', 'policy.policyType'])->findOrFail($id);
        return view('specialist.review', compact('case'));
    }
    
    // Оновлення статусу
    public function updateStatus(Request $request, $id)
    {
        $case = InsuranceCase::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,in_review,approved,rejected',
            'approved_amount' => 'nullable|numeric|min:0',
            'decision_notes' => 'nullable|string',
        ]);
        
        $case->status = $request->status;
        
        if ($request->status == 'approved') {
            $case->approved_amount = $request->approved_amount ?? $case->claim_amount;
            $case->payment_status = 'pending';
            $case->decision_date = now();
        }
        
        if ($request->status == 'rejected') {
            $case->decision_date = now();
        }
        
        if ($request->decision_notes) {
            $case->decision_notes = $request->decision_notes;
        }
        
        $case->save();
        
        $messages = [
            'approved' => 'Випадок схвалено! Сума: ' . number_format($case->approved_amount, 2) . ' грн',
            'rejected' => 'Випадок відхилено',
            'in_review' => 'Випадок взято в роботу',
            'pending' => 'Статус змінено на "Очікує"',
        ];
        
        return redirect()->route('specialist.cases')
            ->with('success', $messages[$request->status]);
    }
    
    // Форма редагування
    public function edit($id)
    {
        $case = InsuranceCase::with(['policy.client'])->findOrFail($id);
        $policies = Policy::with(['client'])->where('status', 'active')->get();
        
        return view('specialist.edit', compact('case', 'policies'));
    }
    
    // Оновлення даних
    public function update(Request $request, $id)
    {
        $case = InsuranceCase::findOrFail($id);
        
        $request->validate([
            'policy_id' => 'required|exists:policies,id',
            'date' => 'required|date',
            'description' => 'required|string|min:10',
            'claim_amount' => 'required|numeric|min:0',
        ]);
        
        $case->update([
            'policy_id' => $request->policy_id,
            'date' => $request->date,
            'description' => $request->description,
            'claim_amount' => $request->claim_amount,
            'assessed_amount' => $request->assessed_amount,
        ]);
        
        return redirect()->route('specialist.cases')
            ->with('success', 'Дані оновлено!');
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
            ->with('success', 'Випадок видалено!');
    }
    
    // Пошук поліса (AJAX)
    public function searchPolicy(Request $request)
    {
        $search = $request->get('q');
        
        $policies = Policy::with(['client'])
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
    public function getPolicy($id)
    {
        $policy = Policy::with(['client'])->findOrFail($id);
        
        return response()->json([
            'id' => $policy->id,
            'policy_number' => $policy->policy_number,
            'client_name' => ($policy->client->last_name ?? '') . ' ' . ($policy->client->first_name ?? ''),
            'client_phone' => $policy->client->phone ?? '-',
            'client_email' => $policy->client->email ?? '-',
        ]);
    }
}