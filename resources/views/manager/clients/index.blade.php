@extends('adminlte::page')

@section('title', 'Клієнти')

@section('content')

<div class="container">

    <h3>Клієнти CRM</h3>

    {{-- 🔍 ФИЛЬТР --}}
    <form method="GET" class="row mb-3">
        <div class="col-md-4">
            <input type="text" name="name" class="form-control"
                   placeholder="Пошук по імені">
        </div>

        <div class="col-md-4">
            <input type="text" name="phone" class="form-control"
                   placeholder="Телефон">
        </div>

        <div class="col-md-4">
            <button class="btn btn-primary">Пошук</button>
        </div>
    </form>

    {{-- 📊 ТАБЛИЦА --}}
    <div class="card">
        <div class="card-body">

            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ПІБ</th>
                        <th>Телефон</th>
                        <th>Email</th>
                        <th>Місто</th>
                        <th width="140">Дії</th>
                    </tr>
                </thead>

                <tbody>

                @foreach($clients as $client)
                    <tr>
                        <td>
                            {{ $client->last_name }} {{ $client->first_name }} {{ $client->middle_name }}
                        </td>

                        <td>{{ $client->phone ?? '—' }}</td>
                        <td>{{ $client->email ?? '—' }}</td>
                        <td>{{ $client->city ?? '—' }}</td>

                        <td>

                            {{-- ✏️ EDIT BUTTON --}}
                            <button class="btn btn-warning btn-sm"
                                    data-toggle="modal"
                                    data-target="#editClientModal{{ $client->id }}">
                                ✏️
                            </button>

                            {{-- 🗑 DELETE --}}
                            <form method="POST"
                                  action="{{ route('manager.clients.destroy', $client) }}"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Видалити клієнта?')">
                                    🗑
                                </button>
                            </form>

                        </td>
                    </tr>

                    {{-- ================= MODAL ================= --}}
                    <div class="modal fade" id="editClientModal{{ $client->id }}" tabindex="-1">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        Редагування клієнта
                                    </h5>

                                    <button type="button" class="close" data-dismiss="modal">
                                        ×
                                    </button>
                                </div>

                                <form method="POST"
                                      action="{{ route('manager.clients.update', $client) }}">

                                    @csrf
                                    @method('PUT')

                                    <div class="modal-body">

                                        {{-- ПІБ --}}
                                        <h6>ПІБ</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <input class="form-control"
                                                       name="last_name"
                                                       value="{{ $client->last_name }}"
                                                       placeholder="Прізвище">
                                            </div>

                                            <div class="col-md-4">
                                                <input class="form-control"
                                                       name="first_name"
                                                       value="{{ $client->first_name }}"
                                                       placeholder="Ім'я">
                                            </div>

                                            <div class="col-md-4">
                                                <input class="form-control"
                                                       name="middle_name"
                                                       value="{{ $client->middle_name }}"
                                                       placeholder="По-батькові">
                                            </div>
                                        </div>

                                        <hr>

                                        {{-- КОНТАКТ --}}
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input class="form-control"
                                                       name="phone"
                                                       value="{{ $client->phone }}"
                                                       placeholder="Телефон">
                                            </div>

                                            <div class="col-md-6">
                                                <input class="form-control"
                                                       name="email"
                                                       value="{{ $client->email }}"
                                                       placeholder="Email">
                                            </div>
                                        </div>

                                        <hr>

                                        {{-- АДРЕСА --}}
                                        <div class="row">
                                            <div class="col-md-4">
                                                <input class="form-control"
                                                       name="country"
                                                       value="{{ $client->country }}"
                                                       placeholder="Країна">
                                            </div>

                                            <div class="col-md-4">
                                                <input class="form-control"
                                                       name="region"
                                                       value="{{ $client->region }}"
                                                       placeholder="Область">
                                            </div>

                                            <div class="col-md-4">
                                                <input class="form-control"
                                                       name="city"
                                                       value="{{ $client->city }}"
                                                       placeholder="Місто">
                                            </div>
                                        </div>

                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <input class="form-control"
                                                       name="street"
                                                       value="{{ $client->street }}"
                                                       placeholder="Вулиця">
                                            </div>

                                            <div class="col-md-3">
                                                <input class="form-control"
                                                       name="house"
                                                       value="{{ $client->house }}"
                                                       placeholder="Будинок">
                                            </div>

                                            <div class="col-md-3">
                                                <input class="form-control"
                                                       name="apartment"
                                                       value="{{ $client->apartment }}"
                                                       placeholder="Квартира">
                                            </div>
                                        </div>

                                        <hr>

                                        {{-- ПАСПОРТ --}}
                                        <div class="row">
                                            <div class="col-md-3">
                                                <input class="form-control"
                                                       name="passport_series"
                                                       value="{{ $client->passport_series }}"
                                                       placeholder="Серія">
                                            </div>

                                            <div class="col-md-3">
                                                <input class="form-control"
                                                       name="passport_number"
                                                       value="{{ $client->passport_number }}"
                                                       placeholder="Номер">
                                            </div>

                                            <div class="col-md-3">
                                                <input class="form-control"
                                                       name="passport_issued_by"
                                                       value="{{ $client->passport_issued_by }}"
                                                       placeholder="Ким виданий">
                                            </div>

                                            <div class="col-md-3">
                                                <input type="date"
                                                       class="form-control"
                                                       name="passport_issued_at"
                                                       value="{{ $client->passport_issued_at }}">
                                            </div>
                                        </div>

                                        <hr>

                                        {{-- ДОПОЛНИТЕЛЬНО --}}
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input class="form-control"
                                                       name="birth_date"
                                                       value="{{ $client->birth_date }}"
                                                       type="date">
                                            </div>

                                            <div class="col-md-6">
                                                <input class="form-control"
                                                       name="tax_number"
                                                       value="{{ $client->tax_number }}"
                                                       placeholder="ІПН">
                                            </div>
                                        </div>

                                    </div>

                                    <div class="modal-footer">
                                        <button class="btn btn-success">
                                            Зберегти
                                        </button>
                                    </div>

                                </form>

                            </div>
                        </div>
                    </div>
                    {{-- ================= /MODAL ================= --}}

                @endforeach

                </tbody>
            </table>

            {{-- 📄 PAGINATION --}}
            {{ $clients->withQueryString()->links() }}

        </div>
    </div>

</div>

@endsection