<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use App\Models\Client;
use App\Models\PolicyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        // Загальна статистика
        $totalPolicies = Policy::count();
        $totalClients = Client::count();
        $activePolicies = Policy::where('status', 'active')->count();
        $expiredPolicies = Policy::where('status', 'expired')->count();
        $cancelledPolicies = Policy::where('status', 'cancelled')->count();
        
        // Фінансова статистика
        $totalPremium = Policy::sum('premium');
        $averagePremium = Policy::avg('premium');
        $totalActivePremium = Policy::where('status', 'active')->sum('premium');
        
        // Статистика по типах полісів
        $policiesByType = PolicyType::withCount('policies')
            ->withSum('policies', 'premium')
            ->get();
        
        // Топ 5 клієнтів за сумою премій
        $topClients = Client::withSum('policies', 'premium')
            ->having('policies_sum_premium', '>', 0)
            ->orderBy('policies_sum_premium', 'desc')
            ->limit(5)
            ->get();
        
        // Замість місячної статистики - статистика за статусами
        $statusStats = [
            'active' => $activePolicies,
            'expired' => $expiredPolicies,
            'cancelled' => $cancelledPolicies,
        ];
        
        // Поліси, що закінчуються найближчим часом (наступні 30 днів)
        $expiringSoon = Policy::with('client', 'policyType')
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->where('end_date', '<=', now()->addDays(30))
            ->orderBy('end_date', 'asc')
            ->limit(10)
            ->get();
        
        // Прострочені поліси
        $overduePolicies = Policy::with('client', 'policyType')
            ->where('status', 'active')
            ->where('end_date', '<', now())
            ->count();
        
        // Відсоток активних полісів
        $activePercentage = $totalPolicies > 0 
            ? round(($activePolicies / $totalPolicies) * 100, 2) 
            : 0;
        
        // Середня тривалість полісу в днях
        $avgDuration = Policy::select(DB::raw('AVG(DATEDIFF(end_date, start_date)) as avg_days'))
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->first()
            ->avg_days ?? 0;
        
        return view('manager.analytics.index', compact(
            'totalPolicies',
            'totalClients',
            'activePolicies',
            'expiredPolicies',
            'cancelledPolicies',
            'totalPremium',
            'averagePremium',
            'totalActivePremium',
            'policiesByType',
            'topClients',
            'statusStats',
            'expiringSoon',
            'overduePolicies',
            'activePercentage',
            'avgDuration'
        ));
    }
}