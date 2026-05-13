@extends('adminlte::page')

@section('title', 'Розгляд випадку #' . $case->id)

@section('content_header')
    <h1><i class="fas fa-gavel"></i> Розгляд випадку #{{ $case->id }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info"><h3 class="card-title">Інформація про випадок</h3></div>
            <div class="card-body">
                <table class="table">
                    <tr><th>Дата події:</th><td>{{ Carbon\Carbon::parse($case->date)->format('d.m.Y') }}</td></tr>
                    <td><th>Сума заяви:</th><td>{{ number_format($case->claim_amount, 2) }} грн</td></tr>
                    <tr><th>Оцінена сума:</th><td>{{ $case->assessed_amount ? number_format($case->assessed_amount, 2) . ' грн' : 'Не визначено' }}</td></tr>
                    <tr><th>Опис:</th><td>{{ $case->description }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary"><h3 class="card-title">Прийняти рішення</h3></div>
            <form action="{{ route('specialist.case.status', $case->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label>Статус</label>
                        <select name="status" class="form-control" id="statusSelect" required>
                            <option value="in_review">Взяти в роботу</option>
                            <option value="approved">Схвалити</option>
                            <option value="rejected">Відхилити</option>
                        </select>
                    </div>
                    <div class="form-group" id="amountField" style="display:none;">
                        <label>Сума виплати (грн)</label>
                        <input type="number" name="approved_amount" step="0.01" class="form-control" value="{{ $case->claim_amount }}" max="{{ $case->claim_amount }}">
                        <small>Максимум: {{ number_format($case->claim_amount, 2) }} грн</small>
                    </div>
                    <div class="form-group">
                        <label>Примітки до рішення</label>
                        <textarea name="decision_notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">Зберегти рішення</button>
                    <a href="{{ route('specialist.cases') }}" class="btn btn-default">Скасувати</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('statusSelect').addEventListener('change', function() {
        document.getElementById('amountField').style.display = this.value === 'approved' ? 'block' : 'none';
    });
</script>
@endsection