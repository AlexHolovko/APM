@extends('adminlte::page')

@section('title', 'Заборгованості')

@section('content_header')
    <h1><i class="fas fa-credit-card"></i> Заборгованості клієнтів</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Список заборгованостей</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Дата сфлатежу</th>
                    <th>Номер полісу</th>
                    <th>Клієнт</th>
                    <th>Сума</th>
                </tr>
            </thead>
            <tbody>
                @forelse($debts ?? [] as $debt)
                <tr>
                    <td>{{ $debt->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($debt->date)->format('d.m.Y') }}</td>
                    <td>{{ $debt->policy->policy_number ?? 'N/A' }}</td>
                    <td>{{ $debt->policy->client->last_name ?? '' }} {{ $debt->policy->client->first_name ?? '' }}</td>
                    <td class="text-danger">{{ number_format($debt->amount, 2) }} грн</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Немає заборгованостей</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">ВСЬОГО:</th>
                    <th class="text-danger">{{ number_format($totalDebt ?? 0, 2) }} грн</th>
                </tr>
            </tfoot>
        </table>
        <div class="p-3">{{ $debts->links() }}</div>
    </div>
</div>
@endsection