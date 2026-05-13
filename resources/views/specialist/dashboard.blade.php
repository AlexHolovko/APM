@extends('adminlte::page')

@section('title', 'Панель спеціаліста')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-md text-info"></i> Панель спеціаліста</h1>
        <span class="badge badge-info">{{ now()->format('d.m.Y H:i') }}</span>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $stats['total'] ?? 0 }}</h3><p>Всього випадків</p></div>
            <div class="icon"><i class="fas fa-briefcase-medical"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ ($stats['pending'] ?? 0) + ($stats['in_review'] ?? 0) }}</h3><p>В роботі</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $stats['approved'] ?? 0 }}</h3><p>Схвалено</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ number_format($stats['total_payouts'] ?? 0, 0) }} грн</h3><p>Виплачено</p></div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history"></i> Останні випадки</h3>
        <div class="card-tools">
            <a href="{{ route('specialist.cases') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-list"></i> Всі випадки
            </a>
            <a href="{{ route('specialist.case.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Новий випадок
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Номер полісу</th>
                        <th>Клієнт</th>
                        <th>Дата події</th>
                        <th>Заявлена сума</th>
                        <th>Статус</th>
                        <th width="80">Дії</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentCases ?? [] as $case)
                    <tr>
                        <td><code>#{{ $case->id }}</code></td>
                        <td>{{ $case->policy->policy_number ?? 'N/A' }}</td>
                        <td>
                            {{ $case->policy->client->last_name ?? '' }} 
                            {{ $case->policy->client->first_name ?? '' }}
                            @if($case->policy->client->middle_name)
                                {{ $case->policy->client->middle_name }}
                            @endif
                        </td>
                        <td>
                            @if($case->date)
                                {{ \Carbon\Carbon::parse($case->date)->format('d.m.Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ number_format($case->claim_amount ?? 0, 2) }} грн</td>
                        <td>
                            @php
                                $badges = ['pending'=>'warning','in_review'=>'info','approved'=>'success','rejected'=>'danger'];
                                $texts = ['pending'=>'Очікує','in_review'=>'В роботі','approved'=>'Схвалено','rejected'=>'Відхилено'];
                            @endphp
                            <span class="badge badge-{{ $badges[$case->status] }}">
                                {{ $texts[$case->status] }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('specialist.case.show', $case->id) }}" class="btn btn-info" title="Переглянути">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(in_array($case->status, ['pending', 'in_review']))
                                    <a href="{{ route('specialist.case.review', $case->id) }}" class="btn btn-warning" title="Розгляд">
                                        <i class="fas fa-gavel"></i>
                                    </a>
                                @endif
                                <a href="{{ route('specialist.case.edit', $case->id) }}" class="btn btn-primary" title="Редагувати">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle"></i> Немає страхових випадків
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .table td {
        vertical-align: middle;
    }
    .btn-group-sm .btn {
        margin: 0 2px;
    }
</style>
@endpush