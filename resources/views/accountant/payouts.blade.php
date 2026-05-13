@extends('adminlte::page')

@section('title', 'Виплати')

@section('content_header')
    <h1><i class="fas fa-money-bill-wave"></i> Виплати</h1>
@stop

@section('content')
<!-- Статистика -->
<div class="row">
    <div class="col-md-4">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Очікує виплати</span>
                <span class="info-box-number">{{ number_format($stats['total_pending'] ?? 0, 2) }} грн</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Виплачено</span>
                <span class="info-box-number">{{ number_format($stats['total_completed'] ?? 0, 2) }} грн</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Відхилено</span>
                <span class="info-box-number">{{ number_format($stats['total_rejected'] ?? 0, 2) }} грн</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Список виплат</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-default btn-sm" data-toggle="collapse" data-target="#filterCollapse">
                <i class="fas fa-filter"></i> Фільтр
            </button>
        </div>
    </div>
    
    <div class="collapse" id="filterCollapse">
        <div class="card-body border-bottom">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <label>Статус</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Всі</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Очікує</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Виплачено</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Дата від</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label>Дата до</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label>Номер полісу</label>
                    <input type="text" name="policy_number" class="form-control form-control-sm" placeholder="Пошук..." value="{{ request('policy_number') }}">
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-search"></i> Пошук
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Дата рішення</th>
                        <th>Номер полісу</th>
                        <th>Клієнт</th>
                        <th>Сума</th>
                        <th>Статус виплати</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($payouts ?? []) as $payout)
                    <tr>
                        <td><code>#{{ $payout->id }}</code></td>
                        <td>{{ $payout->decision_date ? $payout->decision_date->format('d.m.Y') : '-' }}</td>
                        <td>{{ $payout->policy->policy_number ?? 'N/A' }}</td>
                        <td>
                            {{ $payout->policy->client->last_name ?? '' }} 
                            {{ $payout->policy->client->first_name ?? '' }}
                        </td>
                        <td><strong class="text-danger">{{ number_format($payout->claim_amount ?? 0, 2) }} грн</strong></td>
                        <td>
                            @php
                                $statusText = ['pending' => 'Очікує', 'paid' => 'Виплачено', 'rejected' => 'Відхилено'];
                                $statusBadge = ['pending' => 'warning', 'paid' => 'success', 'rejected' => 'danger'];
                                $currentStatus = $payout->payment_status ?? 'pending';
                            @endphp
                            <span class="badge badge-{{ $statusBadge[$currentStatus] ?? 'secondary' }}">
                                {{ $statusText[$currentStatus] ?? $currentStatus }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('accountant.payouts.show', $payout->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> Деталі
                            </a>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle"></i> Немає виплат
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($payouts) && method_exists($payouts, 'links'))
        <div class="p-3">
            {{ $payouts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection