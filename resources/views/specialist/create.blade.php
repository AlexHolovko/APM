@extends('adminlte::page')

@section('title', 'Новий страховий випадок')

@section('content_header')
<h1><i class="fas fa-plus-circle text-success"></i> Новий страховий випадок</h1>
@stop

@section('content')
  <div class="row">
    <div class="col-md-8">
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">Форма реєстрації</h3>
        </div>
        <form action="{{ route('specialist.case.store') }}" method="POST">
          @csrf
          @if($errors->any())
            <div class="alert alert-danger">
              <ul>
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          <div class="card-body">
            <div class="form-group">
              <label>Поліс <span class="text-danger">*</span></label>
              <select name="policy_id" class="form-control" required>
                <option value="">Виберіть поліс</option>
                @foreach($policies as $policy)
                  <option value="{{ $policy->id }}">{{ $policy->policy_number }} - {{ $policy->client->last_name }}
                    {{ $policy->client->first_name }} (тел: {{ $policy->client->phone }})</option>
                @endforeach
              </select>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Дата події <span class="text-danger">*</span></label>
                  <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Сума заяви (грн) <span class="text-danger">*</span></label>
                  <input type="number" name="claim_amount" step="0.01" class="form-control" required>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Опис події <span class="text-danger">*</span></label>
              <textarea name="description" class="form-control" rows="5" required
                placeholder="Детальний опис..."></textarea>
            </div>
            <div class="form-group">
              <label>Оцінена сума (грн)</label>
              <input type="number" name="assessed_amount" step="0.01" class="form-control" placeholder="Необов'язково">
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-success">Створити</button>
            <a href="{{ route('specialist.cases') }}" class="btn btn-default">Скасувати</a>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection