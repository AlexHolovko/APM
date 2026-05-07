@extends('adminlte::page')

@section('title', 'Поліси')

@section('content')

<div class="container-fluid">
    <h3>Поліси</h3>

    {{-- CREATE --}}
    <div class="card mb-3">
        <div class="card-header">
            <h4>Додати новий поліс</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('manager.policies.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-3 mb-2">
                        <select name="client_id" class="form-control" required>
                            <option value="">Виберіть клієнта</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">
                                    {{ $client->last_name }} {{ $client->first_name }} (тел: {{ $client->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <select name="policy_type_id" class="form-control" required>
                            <option value="">Виберіть тип полісу</option>
                            @foreach($policyTypes as $type)
                                <option value="{{ $type->id }}" 
                                    data-premium="{{ $type->default_premium }}"
                                    data-duration="{{ $type->duration_months }}">
                                    {{ $type->name }} ({{ $type->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <input type="text" name="policy_number" class="form-control" placeholder="Номер полісу" required>
                    </div>

                    <div class="col-md-2 mb-2">
                        <input type="date" name="start_date" class="form-control" required>
                    </div>

                    <div class="col-md-2 mb-2">
                        <input type="date" name="end_date" class="form-control" required>
                    </div>

                    <div class="col-md-1 mb-2">
                        <button class="btn btn-success w-100">Додати</button>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-3">
                        <input type="number" step="0.01" name="premium" class="form-control" placeholder="Премія (грн)" required>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Клієнт</th>
                            <th>Тип полісу</th>
                            <th>Номер полісу</th>
                            <th>Початок</th>
                            <th>Кінець</th>
                            <th>Премія</th>
                            <th>Статус</th>
                            <th>Дії</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($policies as $policy)
                        <tr>
                            <td>{{ $policy->id }}</td>
                            <td>
                                <strong>{{ $policy->client->last_name ?? 'Немає' }} {{ $policy->client->first_name ?? '' }}</strong><br>
                                <small class="text-muted">
                                    Тел: {{ $policy->client->phone ?? 'Немає' }}
                                </small>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $policy->policyType->name ?? 'Немає' }}
                                </span><br>
                                <small class="text-muted">{{ $policy->policyType->code ?? '' }}</small>
                            </td>
                            <td>{{ $policy->policy_number }}</td>
                            <td>{{ $policy->start_date->format('d.m.Y') }}</td>
                            <td>{{ $policy->end_date->format('d.m.Y') }}</td>
                            <td><strong>{{ number_format($policy->premium, 2) }}</strong> грн</td>
                            <td>
                                @if($policy->status == 'active')
                                    <span class="badge badge-success">Активний</span>
                                @elseif($policy->status == 'expired')
                                    <span class="badge badge-danger">Прострочений</span>
                                @else
                                    <span class="badge badge-warning">Скасований</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-warning"
                                            data-toggle="modal"
                                            data-target="#edit{{ $policy->id }}">
                                        <i class="fas fa-edit"></i> ✏️
                                    </button>

                                    <form method="POST"
                                          action="{{ route('manager.policies.destroy', $policy) }}"
                                          style="display:inline;"
                                          onsubmit="return confirm('Ви впевнені?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger">
                                            <i class="fas fa-trash"></i> 🗑
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- EDIT MODAL --}}
                        <div class="modal fade" id="edit{{ $policy->id }}">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Редагувати поліс №{{ $policy->policy_number }}</h5>
                                        <button type="button" class="close" data-dismiss="modal">
                                            <span>&times;</span>
                                        </button>
                                    </div>

                                    <form method="POST"
                                          action="{{ route('manager.policies.update', $policy) }}">
                                        @csrf
                                        @method('PUT')

                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Клієнт</label>
                                                        <select name="client_id" class="form-control" required>
                                                            <option value="">Виберіть клієнта</option>
                                                            @foreach($clients as $client)
                                                                <option value="{{ $client->id }}" 
                                                                    {{ $policy->client_id == $client->id ? 'selected' : '' }}>
                                                                    {{ $client->last_name }} {{ $client->first_name }} (тел: {{ $client->phone }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Тип полісу</label>
                                                        <select name="policy_type_id" class="form-control" required>
                                                            <option value="">Виберіть тип полісу</option>
                                                            @foreach($policyTypes as $type)
                                                                <option value="{{ $type->id }}" 
                                                                    {{ $policy->policy_type_id == $type->id ? 'selected' : '' }}>
                                                                    {{ $type->name }} ({{ $type->code }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Номер полісу</label>
                                                        <input name="policy_number"
                                                               value="{{ $policy->policy_number }}"
                                                               class="form-control"
                                                               required>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Премія (грн)</label>
                                                        <input name="premium"
                                                               value="{{ $policy->premium }}"
                                                               class="form-control"
                                                               required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Дата початку</label>
                                                        <input type="date"
                                                               name="start_date"
                                                               value="{{ $policy->start_date->format('Y-m-d') }}"
                                                               class="form-control"
                                                               required>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Дата закінчення</label>
                                                        <input type="date"
                                                               name="end_date"
                                                               value="{{ $policy->end_date->format('Y-m-d') }}"
                                                               class="form-control"
                                                               required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label>Статус</label>
                                                <select name="status" class="form-control">
                                                    <option value="active" {{ $policy->status == 'active' ? 'selected' : '' }}>
                                                        Активний
                                                    </option>
                                                    <option value="expired" {{ $policy->status == 'expired' ? 'selected' : '' }}>
                                                        Прострочений
                                                    </option>
                                                    <option value="cancelled" {{ $policy->status == 'cancelled' ? 'selected' : '' }}>
                                                        Скасований
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                Скасувати
                                            </button>
                                            <button type="submit" class="btn btn-success">
                                                Зберегти зміни
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    // Автоматичне заповнення премії та дат при виборі типу полісу
    document.addEventListener('DOMContentLoaded', function() {
        const policyTypeSelect = document.querySelector('select[name="policy_type_id"]');
        const premiumInput = document.querySelector('input[name="premium"]');
        const startDateInput = document.querySelector('input[name="start_date"]');
        const endDateInput = document.querySelector('input[name="end_date"]');
        
        if (policyTypeSelect) {
            policyTypeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const defaultPremium = selectedOption.dataset.premium;
                const durationMonths = parseInt(selectedOption.dataset.duration);
                
                if (defaultPremium && premiumInput) {
                    premiumInput.value = defaultPremium;
                }
                
                if (durationMonths && startDateInput && endDateInput) {
                    const startDate = new Date();
                    const endDate = new Date();
                    endDate.setMonth(endDate.getMonth() + durationMonths);
                    
                    startDateInput.value = startDate.toISOString().split('T')[0];
                    endDateInput.value = endDate.toISOString().split('T')[0];
                }
            });
        }
    });
</script>
@endpush

@push('css')
<style>
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .btn-group {
        gap: 5px;
    }
    
    .badge {
        font-size: 12px;
        padding: 5px 10px;
    }
    
    @media (max-width: 768px) {
        .table td, .table th {
            padding: 8px;
            font-size: 12px;
        }
        
        .btn-group-sm .btn {
            padding: 4px 8px;
            font-size: 12px;
        }
    }
</style>
@endpush