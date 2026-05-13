@extends('adminlte::page')

@section('title', 'Страхові випадки')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-briefcase-medical"></i> Страхові випадки</h1>
        <a href="{{ route('specialist.case.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Новий випадок</a>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <div class="btn-group">
            <a href="?status=all" class="btn btn-sm btn-default">Всі</a>
            <a href="?status=pending" class="btn btn-sm btn-warning">Очікує</a>
            <a href="?status=in_review" class="btn btn-sm btn-info">В роботі</a>
            <a href="?status=approved" class="btn btn-sm btn-success">Схвалені</a>
            <a href="?status=rejected" class="btn btn-sm btn-danger">Відхилені</a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-striped">
            <thead>
                <tr><th>ID</th><th>Поліс</th><th>Клієнт</th><th>Дата події</th><th>Сума заяви</th><th>Статус</th><th>Дії</th></tr>
            </thead>
            <tbody>
                @forelse($cases as $case)
                <tr>
                    <td><code>#{{ $case->id }}</code></td>
                    <td>{{ $case->policy->policy_number ?? 'N/A' }}</td>
                    <td>{{ $case->policy->client->last_name ?? '' }} {{ $case->policy->client->first_name ?? '' }}</td>
                    <td>{{ $case->date ? \Carbon\Carbon::parse($case->date)->format('d.m.Y') : '-' }}</td>
                    <td>{{ number_format($case->claim_amount ?? 0, 2) }} грн</td>
                    <td>
                        @php
                            $badges = ['pending'=>'warning','in_review'=>'info','approved'=>'success','rejected'=>'danger'];
                            $texts = ['pending'=>'Очікує','in_review'=>'В роботі','approved'=>'Схвалено','rejected'=>'Відхилено'];
                        @endphp
                        <span class="badge badge-{{ $badges[$case->status] }}">{{ $texts[$case->status] }}</span>
                    </td>
                    <td>
                        <a href="{{ route('specialist.case.show', $case->id) }}" class="btn btn-sm btn-info">Деталі</a>
                        <a href="{{ route('specialist.case.review', $case->id) }}" class="btn btn-sm btn-warning">Розгляд</a>
                        <a href="{{ route('specialist.case.edit', $case->id) }}" class="btn btn-sm btn-primary">Ред</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center">Немає даних</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $cases->links() }}</div>
    </div>
</div>
@endsection