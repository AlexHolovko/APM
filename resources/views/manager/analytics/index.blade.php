@extends('adminlte::page')

@section('title', 'Аналітика та звіти')

@section('content')
<div class="container-fluid">
    
    <div class="row mb-4">
        <div class="col-12">
            <h2>📊 Аналітична панель</h2>
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

    {{-- Графіки --}}
    <div class="row mt-4">
        {{-- Кругова діаграма: розподіл полісів за типами --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">📊 Розподіл полісів за типами</h5>
                </div>
                <div class="card-body">
                    <canvas id="policiesByTypeChart" height="250"></canvas>
                </div>
            </div>
        </div>

        {{-- Кругова діаграма: статуси полісів --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">📈 Статус полісів</h5>
                </div>
                <div class="card-body">
                    <canvas id="policyStatusChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        {{-- Стовпчикова діаграма: Топ клієнтів за сумою премій --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">🏆 Топ 5 клієнтів за сумою премій</h5>
                </div>
                <div class="card-body">
                    <canvas id="topClientsChart" height="250"></canvas>
                </div>
            </div>
        </div>

        {{-- Лінійний графік: Динаміка премій за типами полісів --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">📉 Сума премій за типами полісів</h5>
                </div>
                <div class="card-body">
                    <canvas id="premiumByTypeChart" height="250"></canvas>
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
    
    .list-group-item {
        font-size: 16px;
    }
    
    canvas {
        max-height: 300px;
        width: 100% !important;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Кругова діаграма: розподіл полісів за типами
        const typeNames = @json($policiesByType->pluck('name'));
        const typeCounts = @json($policiesByType->pluck('policies_count'));
        
        new Chart(document.getElementById('policiesByTypeChart'), {
            type: 'pie',
            data: {
                labels: typeNames,
                datasets: [{
                    data: typeCounts,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw} полісів (${((ctx.raw / {{ $totalPolicies }}) * 100).toFixed(1)}%)` } }
                }
            }
        });

        // 2. Кругова діаграма: статуси полісів
        new Chart(document.getElementById('policyStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Активні', 'Прострочені', 'Скасовані'],
                datasets: [{
                    data: [{{ $activePolicies }}, {{ $expiredPolicies }}, {{ $cancelledPolicies }}],
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw} полісів (${((ctx.raw / {{ $totalPolicies }}) * 100).toFixed(1)}%)` } }
                }
            }
        });

        // 3. Стовпчикова діаграма: Топ 5 клієнтів
        const clientNames = @json($topClients->map(fn($c) => $c->last_name . ' ' . $c->first_name));
        const clientPremiums = @json($topClients->pluck('policies_sum_premium'));
        
        new Chart(document.getElementById('topClientsChart'), {
            type: 'bar',
            data: {
                labels: clientNames,
                datasets: [{
                    label: 'Сума премій (грн)',
                    data: clientPremiums,
                    backgroundColor: '#36A2EB',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: { y: { beginAtZero: true, title: { display: true, text: 'грн' } } },
                plugins: { tooltip: { callbacks: { label: (ctx) => `${ctx.raw.toFixed(2)} грн` } } }
            }
        });

        // 4. Стовпчикова діаграма: Сума премій за типами полісів
        const typePremiums = @json($policiesByType->pluck('policies_sum_premium'));
        
        new Chart(document.getElementById('premiumByTypeChart'), {
            type: 'bar',
            data: {
                labels: typeNames,
                datasets: [{
                    label: 'Сума премій (грн)',
                    data: typePremiums,
                    backgroundColor: '#FF9F40',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: { y: { beginAtZero: true, title: { display: true, text: 'грн' } } },
                plugins: { tooltip: { callbacks: { label: (ctx) => `${ctx.raw.toFixed(2)} грн` } } }
            }
        });
    });
</script>
@endpush