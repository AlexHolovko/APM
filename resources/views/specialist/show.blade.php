@extends('adminlte::page')

@section('title', 'Перегляд випадку #' . $case->id)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-info-circle text-info"></i> Перегляд випадку #{{ $case->id }}</h1>
        <a href="{{ route('specialist.cases') }}" class="btn btn-default">
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
                    <i class="fas fa-info-circle"></i> Інформація про випадок
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 40%;">ID випадку:</th>
                        <td><code>#{{ $case->id }}</code></td>
                    </tr>
                    <tr>
                        <th>Дата події:</th>
                        <td>{{ $case->date ? \Carbon\Carbon::parse($case->date)->format('d.m.Y') : '-' }}</td>
                    </tr>
                    @if($case->assessed_amount)
                    <tr>
                        <th>Оцінена сума:</th>
                        <td>{{ number_format($case->assessed_amount, 2) }} грн</strong></td>
                    </tr>
                    @endif
                    <tr>
                        <th>Статус:</th>
                        <td>
                            @php
                                $badges = ['pending'=>'warning','in_review'=>'info','approved'=>'success','rejected'=>'danger'];
                                $texts = ['pending'=>'Очікує','in_review'=>'В роботі','approved'=>'Схвалено','rejected'=>'Відхилено'];
                            @endphp
                            <span class="badge badge-{{ $badges[$case->status] }}">
                                {{ $texts[$case->status] }}
                            </span>
                        </td>
                    </tr>
                    @if($case->decision_date)
                    <tr>
                        <th>Дата рішення:</th>
                        <td>{{ \Carbon\Carbon::parse($case->decision_date)->format('d.m.Y') }}</td>
                    </tr>
                    @endif
                    @if($case->decision_notes)
                    <tr>
                        <th>Примітки до рішення:</th>
                        <td>{{ $case->decision_notes }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Опис події:</th>
                        <td>{{ $case->description }}</td>
                    </tr>
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
                        <td><code>{{ $case->policy->policy_number ?? 'N/A' }}</code></td>
                    </tr>
                    <tr>
                        <th>Тип полісу:</th>
                        <td>{{ $case->policy->policyType->name ?? 'N/A' }}</td>
                    </tr>
                    @if($case->policy->start_date)
                    <tr>
                        <th>Дата початку:</th>
                        <td>{{ \Carbon\Carbon::parse($case->policy->start_date)->format('d.m.Y') }}</td>
                    </tr>
                    @endif
                    @if($case->policy->end_date)
                    <tr>
                        <th>Дата закінчення:</th>
                        <td>{{ \Carbon\Carbon::parse($case->policy->end_date)->format('d.m.Y') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Страхова премія:</th>
                        <td>{{ number_format($case->policy->premium ?? 0, 2) }} грн</strong></td>
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
                            {{ $case->policy->client->last_name ?? '' }} 
                            {{ $case->policy->client->first_name ?? '' }}
                            {{ $case->policy->client->middle_name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Телефон:</th>
                        <td>{{ $case->policy->client->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $case->policy->client->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Адреса:</th>
                        <td>
                            @if($case->policy->client->city)
                                {{ $case->policy->client->city }}, 
                                {{ $case->policy->client->street ?? '' }}, 
                                {{ $case->policy->client->house ?? '' }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="card-footer">
            <a href="{{ route('specialist.cases') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
            @if(in_array($case->status, ['pending', 'in_review']))
                <a href="{{ route('specialist.case.review', $case->id) }}" class="btn btn-warning">
                    <i class="fas fa-gavel"></i> Розгляд
                </a>
            @endif
            <a href="{{ route('specialist.case.edit', $case->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Редагувати
            </a>
        </div>
    </div>
</div>
@endsection