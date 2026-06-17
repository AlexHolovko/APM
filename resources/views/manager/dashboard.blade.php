@extends('adminlte::page')

@section('title', 'Панель менеджера')

@section('content_header')
    <h1><i class="fas fa-chart-line"></i> Панель менеджера</h1>
@stop

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-plus"></i> Додати нового клієнта</h3>
            </div>
            <div class="card-body">

                <form method="POST" action="{{ route('manager.clients.store') }}">
                    @csrf

                    {{-- ПІБ --}}
                    <div class="row">
                        <div class="col-md-4">
                            <label>Прізвище <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label>Ім'я <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label>По-батькові</label>
                            <input type="text" name="middle_name" class="form-control">
                        </div>
                    </div>

                    {{-- Дата + ІПН --}}
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label>Дата народження</label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>ІПН</label>
                            <input type="text" name="tax_number" class="form-control">
                        </div>
                    </div>

                    <hr>

                    {{-- Адреса --}}
                    <div class="row">
                        <div class="col-md-4">
                            <label>Країна</label>
                            <input type="text" name="country" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Область</label>
                            <input type="text" name="region" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Місто</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label>Вулиця</label>
                            <input type="text" name="street" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Будинок</label>
                            <input type="text" name="house" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Квартира</label>
                            <input type="text" name="apartment" class="form-control">
                        </div>
                    </div>

                    <hr>

                    {{-- Паспорт --}}
                    <div class="row">
                        <div class="col-md-3">
                            <label>Серія</label>
                            <input type="text" name="passport_series" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Номер</label>
                            <input type="text" name="passport_number" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Ким виданий</label>
                            <input type="text" name="passport_issued_by" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Дата видачі</label>
                            <input type="date" name="passport_issued_at" class="form-control">
                        </div>
                    </div>

                    <hr>

                    {{-- Контакти --}}
                    <div class="row">
                        <div class="col-md-6">
                            <label>Телефон</label>
                            <input type="text" name="phone" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success mt-3">
                        <i class="fas fa-save"></i> Додати клієнта
                    </button>

                </form>

            </div>
        </div>
    </div>
</div>



<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clock"></i> Останні 10 клієнтів</h3>
                <div class="card-tools">
                    <a href="{{ route('manager.clients.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-list"></i> Всі клієнти
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ПІБ</th>
                                <th>Телефон</th>
                                <th>Email</th>
                                <th>Місто</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestClients as $index => $client)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $client->last_name }} {{ $client->first_name }} {{ $client->middle_name }}</strong>
                                </td>
                                <td>{{ $client->phone ?? '—' }}</td>
                                <td>{{ $client->email ?? '—' }}</td>
                                <td>{{ $client->city ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle"></i> Немає клієнтів
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
    .info-box {
        min-height: 100px;
        margin-bottom: 20px;
        padding: 15px;
        border-radius: 5px;
    }
    .info-box-icon {
        border-radius: 5px;
        display: block;
        float: left;
        height: 70px;
        width: 70px;
        text-align: center;
        font-size: 30px;
        line-height: 70px;
        background: rgba(0,0,0,0.2);
    }
    .info-box-content {
        margin-left: 85px;
    }
    .info-box-text {
        font-size: 14px;
        text-transform: uppercase;
        font-weight: 600;
    }
    .info-box-number {
        font-size: 24px;
        font-weight: 700;
    }
    .bg-info {
        background-color: #17a2b8;
        color: white;
    }
    .bg-success {
        background-color: #28a745;
        color: white;
    }
    .bg-warning {
        background-color: #ffc107;
        color: white;
    }
    .bg-danger {
        background-color: #dc3545;
        color: white;
    }
</style>
@endpush