@extends('adminlte::page')

@section('title', 'Панель менеджера')

@section('content')

  <div class="container">

    <h3>Панель менеджера</h3>
    <div class="card-body">

      <div class="card-body">

        <form method="POST" action="{{ route('manager.clients.store') }}">
          @csrf

          {{-- ПІБ --}}
          <div class="row">
            <div class="col-md-4">
              <label>Прізвище</label>
              <input type="text" name="last_name" class="form-control" required>
            </div>

            <div class="col-md-4">
              <label>Ім'я</label>
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

          <button class="btn btn-success mt-3">
            Додати клієнта
          </button>

        </form>

      </div>
      <h4>{{ $clientsCount }}</h4>
      <p>Всього клієнтів</p>
      <div class="card mt-3">
        <div class="card-header">
          Останні клієнти
        </div>

        <div class="card-body">

          <div class="row">
            @foreach($latestClients as $client)
              <div class="col-md-6 mb-2">

                <div class="card border">

                  <div class="card-body">

                    {{-- Имя --}}
                    <h5 class="mb-1">
                      {{ $client->last_name }} {{ $client->first_name }}
                    </h5>

                    {{-- Контакт --}}
                    <div class="text-muted">
                      📞 {{ $client->phone ?? '—' }} <br>
                      ✉️ {{ $client->email ?? '—' }}
                    </div>

                    {{-- Локация --}}
                    <small class="text-muted">
                      {{ $client->city ?? '' }} {{ $client->street ?? '' }}
                    </small>

                  </div>

                </div>

              </div>
            @endforeach
          </div>

        </div>
      </div>
    </div>

@endsection