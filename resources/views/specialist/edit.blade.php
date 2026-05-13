@extends('adminlte::page')

@section('title', 'Редагування випадку #' . $case->id)

@section('content_header')
    <h1><i class="fas fa-edit text-warning"></i> Редагування випадку #{{ $case->id }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-warning">
            <div class="card-header"><h3 class="card-title">Форма редагування</h3></div>
            <form action="{{ route('specialist.case.update', $case->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label>Поліс</label>
                        <select name="policy_id" class="form-control" required>
                            @foreach($policies as $policy)
                                <option value="{{ $policy->id }}" {{ $case->policy_id == $policy->id ? 'selected' : '' }}>{{ $policy->policy_number }} - {{ $policy->client->last_name }} {{ $policy->client->first_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Дата події</label>
                                <input type="date" name="date" class="form-control" value="{{ $case->date ? Carbon\Carbon::parse($case->date)->format('Y-m-d') : '' }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Сума заяви (грн)</label>
                                <input type="number" name="claim_amount" step="0.01" class="form-control" value="{{ $case->claim_amount }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Опис події</label>
                        <textarea name="description" class="form-control" rows="5" required>{{ $case->description }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Оцінена сума (грн)</label>
                        <input type="number" name="assessed_amount" step="0.01" class="form-control" value="{{ $case->assessed_amount }}">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Зберегти</button>
                    <a href="{{ route('specialist.cases') }}" class="btn btn-default">Скасувати</a>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" action="{{ route('specialist.case.destroy', $case->id) }}" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>

@push('js')
<script>
    function deleteCase() {
        if (confirm('Ви впевнені, що хочете видалити цей випадок?')) {
            document.getElementById('deleteForm').submit();
        }
    }
</script>
@endpush
@endsection