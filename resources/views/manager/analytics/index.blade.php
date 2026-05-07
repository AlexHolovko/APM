@extends('adminlte::page')

@section('title', 'Аналітика та звіти')

@section('content')
<div class="container-fluid">
    
    <div class="row mb-4">
        <div class="col-12">
            <h2>📊 Аналітична дашборда</h2>
            <p class="text-muted">Ключові метрики та статистика вашого страхового портфелю</p>
        </div>
    </div>

    {{-- Ключові метрики --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalPremium, 2) }} грн</h3>
                    <p>Загальна сума премій</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalPolicies }}</h3>
                    <p>Всього полісів</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-contract"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalClients }}</h3>
                    <p>Всього клієнтів</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $activePercentage }}%</h3>
                    <p>Активних полісів</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Друга лінія метрик --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($totalActivePremium, 2) }} грн</h3>
                    <p>Активних премій</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ number_format($averagePremium, 2) }} грн</h3>
                    <p>Середня премія</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calculator"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-dark">
                <div class="inner">
                    <h3>{{ $expiredPolicies + $cancelledPolicies }}</h3>
                    <p>Неактивних полісів</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $overduePolicies }}</h3>
                    <p>Прострочених полісів</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Графіки та таблиці --}}
    <div class="row mt-4">
        {{-- Статистика по типах полісів --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">📋 Поліси за типами</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Тип полісу</th>
                                    <th>Кількість</th>
                                    <th>Сума премій</th>
                                    <th>Середня</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($policiesByType as $type)
                                <tr>
                                    <td>{{ $type->name }}</td>
                                    <td>{{ $type->policies_count ?? 0 }}</td>
                                    <td>{{ number_format($type->policies_sum_premium ?? 0, 2) }} грн</td>
                                    <td>
                                        @if(($type->policies_count ?? 0) > 0)
                                            {{ number_format(($type->policies_sum_premium ?? 0) / $type->policies_count, 2) }} грн
                                        @else
                                            0 грн
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center">Немає даних</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Топ клієнтів --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">🏆 Топ 5 клієнтів за сумою премій</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Клієнт</th>
                                    <th>Телефон</th>
                                    <th>Сума премій</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topClients as $client)
                                <tr>
                                    <td>{{ $client->last_name }} {{ $client->first_name }}</td>
                                    <td>{{ $client->phone }}</td>
                                    <td>{{ number_format($client->policies_sum_premium ?? 0, 2) }} грн</td>
                                </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center">Немає даних</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        {{-- Поліси, що закінчуються --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="card-title">⚠️ Поліси, що закінчуються найближчим часом</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Клієнт</th>
                                    <th>Тип полісу</th>
                                    <th>Дата закінчення</th>
                                    <th>Днів до кінця</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expiringSoon as $policy)
                                @php
                                    $daysLeft = now()->diffInDays($policy->end_date, false);
                                @endphp
                                <tr>
                                    <td>{{ $policy->client->last_name ?? '' }} {{ $policy->client->first_name ?? '' }}</td>
                                    <td>{{ $policy->policyType->name ?? '' }}</td>
                                    <td>{{ $policy->end_date->format('d.m.Y') }}</td>
                                    <td>
                                        @if($daysLeft <= 7)
                                            <span class="badge badge-danger">{{ $daysLeft }} днів</span>
                                        @elseif($daysLeft <= 14)
                                            <span class="badge badge-warning">{{ $daysLeft }} днів</span>
                                        @else
                                            <span class="badge badge-info">{{ $daysLeft }} днів</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center">Немає полісів, що закінчуються</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Додаткова інформація --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info">
                    <h5 class="card-title">ℹ️ Додаткова статистика</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Середня тривалість полісу
                            <span class="badge badge-primary badge-pill">{{ round($avgDuration) }} днів</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Співвідношення активних до всіх
                            <span class="badge badge-success badge-pill">{{ $activePolicies }} / {{ $totalPolicies }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Прострочених активних полісів
                            <span class="badge badge-danger badge-pill">{{ $overduePolicies }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Середня премія на клієнта
                            <span class="badge badge-info badge-pill">
                                @if($totalClients > 0)
                                    {{ number_format($totalPremium / $totalClients, 2) }} грн
                                @else
                                    0 грн
                                @endif
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Статус полісів у вигляді прогрес-бару --}}
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">📊 Статус полісів</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="progress mb-2">
                                <div class="progress-bar bg-success" style="width: {{ $activePercentage }}%">
                                    {{ $activePercentage }}%
                                </div>
                            </div>
                            <p>Активні: {{ $activePolicies }}</p>
                        </div>
                        <div class="col-md-4">
                            <div class="progress mb-2">
                                <div class="progress-bar bg-danger" style="width: {{ $totalPolicies > 0 ? ($expiredPolicies / $totalPolicies) * 100 : 0 }}%">
                                    {{ $totalPolicies > 0 ? round(($expiredPolicies / $totalPolicies) * 100, 2) : 0 }}%
                                </div>
                            </div>
                            <p>Прострочені: {{ $expiredPolicies }}</p>
                        </div>
                        <div class="col-md-4">
                            <div class="progress mb-2">
                                <div class="progress-bar bg-warning" style="width: {{ $totalPolicies > 0 ? ($cancelledPolicies / $totalPolicies) * 100 : 0 }}%">
                                    {{ $totalPolicies > 0 ? round(($cancelledPolicies / $totalPolicies) * 100, 2) : 0 }}%
                                </div>
                            </div>
                            <p>Скасовані: {{ $cancelledPolicies }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('css')
<style>
    .small-box {
        border-radius: 10px;
        transition: transform 0.3s;
    }
    
    .small-box:hover {
        transform: translateY(-5px);
    }
    
    .small-box .icon {
        font-size: 70px;
    }
    
    .progress {
        height: 30px;
        border-radius: 15px;
    }
    
    .progress-bar {
        line-height: 30px;
        font-weight: bold;
    }
    
    .list-group-item {
        font-size: 16px;
    }
</style>
@endpush