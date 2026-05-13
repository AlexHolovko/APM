@extends('adminlte::page')

@section('title', 'Деталі виплати')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-receipt"></i> Деталі виплати #{{ $payout->id }}</h1>
        <a href="{{ route('accountant.payouts') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Назад до списку
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Інформація про виплату
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 40%;">ID виплати:</th>
                        <td><code>#{{ $payout->id }}</code></td>
                    </tr>
                    <tr>
                        <th>Дата створення:</th>
                        <td>{{ $payout->created_at->format('d.m.Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Сума:</th>
                        <td><strong class="text-danger h4">{{ number_format($payout->amount, 2) }} грн</strong></td>
                    </tr>
                    <tr>
                        <th>Статус:</th>
                        <td>
                            @php
                                $statusText = ['pending' => 'Очікує', 'completed' => 'Виплачено', 'rejected' => 'Відхилено'];
                                $statusBadge = ['pending' => 'warning', 'completed' => 'success', 'rejected' => 'danger'];
                            @endphp
                            <span class="badge badge-{{ $statusBadge[$payout->status] }} badge-lg">
                                {{ $statusText[$payout->status] }}
                            </span>
                        </td>
                    </tr>
                    @if($payout->payment_date)
                    <tr>
                        <th>Дата виплати:</th>
                        <td>{{ \Carbon\Carbon::parse($payout->payment_date)->format('d.m.Y') }}</td>
                    </tr>
                    @endif
                    @if($payout->transaction_id)
                    <tr>
                        <th>ID транзакції:</th>
                        <td><code>{{ $payout->transaction_id }}</code></td>
                    </tr>
                    @endif
                    @if($payout->notes)
                    <tr>
                        <th>Примітки:</th>
                        <td>{{ $payout->notes }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">
                    <i class="fas fa-file-contract"></i> Інформація про поліс
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 40%;">Номер полісу:</th>
                        <td><code>{{ $payout->policy->policy_number ?? 'N/A' }}</code></td>
                    </tr>
                    <tr>
                        <th>Тип полісу:</th>
                        <td>{{ $payout->policy->policyType->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Страхова сума:</th>
                        <td>{{ number_format($payout->policy->premium ?? 0, 2) }} грн</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h3 class="card-title">
                    <i class="fas fa-user"></i> Інформація про клієнта
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 40%;">ПІБ:</th>
                        <td>
                            {{ $payout->policy->client->last_name ?? '' }} 
                            {{ $payout->policy->client->first_name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Телефон:</th>
                        <td>{{ $payout->policy->client->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $payout->policy->client->email ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Форма оновлення статусу -->
@if($payout->status == 'pending')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title">
                    <i class="fas fa-edit"></i> Оновити статус виплати
                </h3>
            </div>
            <form action="{{ route('accountant.payouts.update', $payout->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Статус</label>
                                <select name="status" class="form-control" required>
                                    <option value="pending" selected>Очікує</option>
                                    <option value="completed">Виплачено</option>
                                    <option value="rejected">Відхилити</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>ID транзакції</label>
                                <input type="text" name="transaction_id" class="form-control" placeholder="Номер транзакції">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Дата виплати</label>
                                <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Примітки</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Додаткові коментарі..."></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Зберегти зміни
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('css')
<style>
    .badge-lg {
        font-size: 14px;
        padding: 5px 10px;
    }
</style>
@endpush