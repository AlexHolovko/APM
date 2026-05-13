@extends('adminlte::page')

@section('title', 'Деталі виплати')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-money-bill-wave"></i> Деталі виплати #{{ $payout->id }}</h1>
        <a href="{{ route('accountant.payouts') }}" class="btn btn-default btn-sm">
            <i class="fas fa-arrow-left"></i> Назад до списку
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Інформація про страховий випадок
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <tr>
                        <th width="30%">ID випадку:</th>
                        <td><code>#{{ $payout->id }}</code></td>
                    </tr>
                    <tr>
                        <th>Номер полісу:</th>
                        <td><strong>{{ $payout->policy->policy_number ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <th>Тип полісу:</th>
                        <td>{{ $payout->policy->policyType->name ?? 'Не вказано' }}</td>
                    </tr>
                    <tr>
                        <th>Клієнт:</th>
                        <td>
                            {{ $payout->policy->client->last_name ?? '' }} 
                            {{ $payout->policy->client->first_name ?? '' }}
                            @if($payout->policy->client->middle_name ?? '')
                                {{ $payout->policy->client->middle_name }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Телефон клієнта:</th>
                        <td>{{ $payout->policy->client->phone ?? 'Не вказано' }}</td>
                    </tr>
                    <tr>
                        <th>Email клієнта:</th>
                        <td>{{ $payout->policy->client->email ?? 'Не вказано' }}</td>
                    </tr>
                    <tr>
                        <th>Дата страхового випадку:</th>
                        <td>{{ $payout->date ? \Carbon\Carbon::parse($payout->date)->format('d.m.Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Заявлена сума:</th>
                        <td>{{ number_format($payout->claim_amount ?? 0, 2) }} грн</td>
                    </tr>
                    <tr class="table-success">
                        <th>Схвалена сума до виплати:</th>
                        <td><strong class="text-success">{{ number_format($payout->approved_amount ?? 0, 2) }} грн</strong></td>
                    </tr>
                    <tr>
                        <th>Статус виплати:</th>
                        <td>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'paid' => 'success',
                                    'rejected' => 'danger'
                                ];
                                $statusTexts = [
                                    'pending' => 'Очікує виплати',
                                    'paid' => 'Виплачено',
                                    'rejected' => 'Відхилено'
                                ];
                            @endphp
                            <span class="badge badge-{{ $statusColors[$payout->payment_status] ?? 'secondary' }} badge-lg">
                                {{ $statusTexts[$payout->payment_status] ?? $payout->payment_status }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Дата рішення:</th>
                        <td>{{ $payout->decision_date ? \Carbon\Carbon::parse($payout->decision_date)->format('d.m.Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Опис випадку:</th>
                        <td>{{ $payout->description ?? 'Немає опису' }}</td>
                    </tr>
                    <tr>
                        <th>Рішення/Примітки:</th>
                        <td>{{ $payout->decision_notes ?? 'Немає приміток' }}</td>
                    </tr>
                    <tr>
                        <th>Дата створення:</th>
                        <td>{{ $payout->created_at ? \Carbon\Carbon::parse($payout->created_at)->format('d.m.Y H:i') : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Карточка с действиями -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">
                    <i class="fas fa-cogs"></i> Дії
                </h3>
            </div>
            <div class="card-body">
                @if($payout->payment_status == 'pending')
                    <form action="{{ route('accountant.payouts.update', $payout->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="status">Змінити статус виплати:</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="pending" selected>Очікує виплати</option>
                                <option value="paid">Виплачено</option>
                                <option value="rejected">Відхилити</option>
                            </select>
                        </div>

                        <div class="form-group" id="transactionIdGroup" style="display: none;">
                            <label for="transaction_id">ID транзакції:</label>
                            <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="Введіть ID транзакції">
                        </div>

                        <div class="form-group" id="paymentDateGroup" style="display: none;">
                            <label for="payment_date">Дата виплати:</label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="form-group">
                            <label for="notes">Примітки:</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Додаткові примітки..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Зберегти зміни
                        </button>
                    </form>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        @if($payout->payment_status == 'paid')
                            Цю виплату вже проведено. Не можна змінити статус.
                        @elseif($payout->payment_status == 'rejected')
                            Цю виплату відхилено. Не можна змінити статус.
                        @endif
                    </div>
                    <a href="{{ route('accountant.payouts') }}" class="btn btn-default btn-block">
                        <i class="fas fa-arrow-left"></i> Повернутись до списку
                    </a>
                @endif
            </div>
        </div>

        <!-- Карточка с суммой -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">
                    <i class="fas fa-calculator"></i> Фінансова інформація
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <h4>Сума до виплати</h4>
                    <h2 class="text-success">{{ number_format($payout->approved_amount ?? 0, 2) }} грн</h2>
                    <hr>
                    <p>
                        <small>
                            <i class="fas fa-clock"></i> 
                            Статус: {{ $statusTexts[$payout->payment_status] ?? $payout->payment_status }}
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.getElementById('status')?.addEventListener('change', function() {
        const transactionGroup = document.getElementById('transactionIdGroup');
        const paymentDateGroup = document.getElementById('paymentDateGroup');
        
        if (this.value === 'paid') {
            transactionGroup.style.display = 'block';
            paymentDateGroup.style.display = 'block';
            document.getElementById('transaction_id').required = true;
            document.getElementById('payment_date').required = true;
        } else {
            transactionGroup.style.display = 'none';
            paymentDateGroup.style.display = 'none';
            document.getElementById('transaction_id').required = false;
            document.getElementById('payment_date').required = false;
        }
    });
</script>
@endpush

@push('css')
<style>
    .badge-lg {
        font-size: 14px;
        padding: 5px 10px;
    }
    .table th {
        background-color: #f8f9fa;
    }
</style>
@endpush