// app/Http/Controllers/PolicyController.php
<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use App\Models\Contract;
use App\Models\User;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PolicyController extends Controller
{
    // Список полисов
    public function index(Request $request)
    {
        $query = Policy::with(['user', 'application', 'contract']);

        // Поиск
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('policy_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Фильтр по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Фильтр по пользователю
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $policies = $query->orderBy('created_at', 'desc')->paginate(15);
        $users = User::all();
        
        return view('policies.index', compact('policies', 'users'));
    }

    // Форма создания
    public function create()
    {
        $users = User::all();
        $applications = Application::all();
        return view('policies.create', compact('users', 'applications'));
    }

    // Сохранение
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'policy_number' => 'required|unique:policies|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'premium' => 'required|numeric|min:0',
            'status' => 'required|in:active,expired,cancelled,pending',
            'user_id' => 'required|exists:users,id',
            'application_id' => 'required|exists:applications,id',
            'contract_number' => 'nullable|unique:contracts|max:50',
            'signed_date' => 'nullable|date',
            'payment_schedule' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        
        try {
            // Создание полиса
            $policy = Policy::create($request->only([
                'policy_number', 'start_date', 'end_date', 'premium', 
                'status', 'user_id', 'application_id'
            ]));
            
            // Создание контракта если указан номер
            if ($request->filled('contract_number')) {
                Contract::create([
                    'contract_number' => $request->contract_number,
                    'signed_date' => $request->signed_date,
                    'payment_schedule' => $request->payment_schedule,
                    'policy_id' => $policy->id
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('policies.index')
                ->with('success', 'Полис успешно создан!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Ошибка при создании: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Просмотр полиса
    public function show(Policy $policy)
    {
        $policy->load(['user', 'application', 'contract']);
        return view('policies.show', compact('policy'));
    }

    // Форма редактирования
    public function edit(Policy $policy)
    {
        $policy->load('contract');
        $users = User::all();
        $applications = Application::all();
        return view('policies.edit', compact('policy', 'users', 'applications'));
    }

    // Обновление
    public function update(Request $request, Policy $policy)
    {
        $validator = Validator::make($request->all(), [
            'policy_number' => 'required|max:50|unique:policies,policy_number,' . $policy->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'premium' => 'required|numeric|min:0',
            'status' => 'required|in:active,expired,cancelled,pending',
            'user_id' => 'required|exists:users,id',
            'application_id' => 'required|exists:applications,id',
            'contract_number' => 'nullable|max:50|unique:contracts,contract_number,' . ($policy->contract->id ?? 'NULL'),
            'signed_date' => 'nullable|date',
            'payment_schedule' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        
        try {
            // Обновление полиса
            $policy->update($request->only([
                'policy_number', 'start_date', 'end_date', 'premium', 
                'status', 'user_id', 'application_id'
            ]));
            
            // Обновление или создание контракта
            if ($request->filled('contract_number')) {
                if ($policy->contract) {
                    $policy->contract->update([
                        'contract_number' => $request->contract_number,
                        'signed_date' => $request->signed_date,
                        'payment_schedule' => $request->payment_schedule
                    ]);
                } else {
                    Contract::create([
                        'contract_number' => $request->contract_number,
                        'signed_date' => $request->signed_date,
                        'payment_schedule' => $request->payment_schedule,
                        'policy_id' => $policy->id
                    ]);
                }
            } elseif ($policy->contract) {
                // Если номер контракта не указан, но он существует - удаляем
                $policy->contract->delete();
            }
            
            DB::commit();
            
            return redirect()->route('policies.index')
                ->with('success', 'Полис успешно обновлен!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Ошибка при обновлении: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Удаление
    public function destroy(Policy $policy)
    {
        try {
            $policy->delete();
            return redirect()->route('policies.index')
                ->with('success', 'Полис успешно удален!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ошибка при удалении: ' . $e->getMessage());
        }
    }

    // API: Обновить статус платежа в контракте
    public function updatePaymentStatus(Request $request, Contract $contract)
    {
        $request->validate([
            'payment_index' => 'required|integer',
            'paid' => 'required|boolean'
        ]);

        $schedule = $contract->payment_schedule;
        
        if (isset($schedule[$request->payment_index])) {
            $schedule[$request->payment_index]['paid'] = $request->paid;
            $contract->payment_schedule = $schedule;
            $contract->save();
            
            return response()->json([
                'success' => true, 
                'message' => 'Статус платежа обновлен',
                'payment_status' => $contract->payment_status_name
            ]);
        }
        
        return response()->json(['error' => 'Платеж не найден'], 404);
    }
} 