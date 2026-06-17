@extends('adminlte::page')

@section('title', 'Поліси')

@section('content_header')
    <h1><i class="fas fa-file-contract"></i> Поліси</h1>
@stop

@section('content')

<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- CREATE FORM --}}
    <div class="card mb-3">
        <div class="card-header">
            <h4>Додати новий поліс</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('manager.policies.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Клієнт</label>
                        <select name="client_id" class="form-control @error('client_id') is-invalid @enderror" required>
                            <option value="">Виберіть клієнта</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">
                                    {{ $client->last_name }} {{ $client->first_name }} (тел: {{ $client->phone }})
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Тип полісу</label>
                        <select name="policy_type_id" id="policy_type_id" class="form-control @error('policy_type_id') is-invalid @enderror" required>
                            <option value="">Виберіть тип полісу</option>
                            @foreach($policyTypes as $type)
                                <option value="{{ $type->id }}" 
                                    data-premium="{{ $type->default_premium }}"
                                    data-duration="{{ $type->duration_months }}"
                                    data-franchise-value="{{ $type->franchise_value }}"
                                    data-franchise-type="{{ $type->franchise_type }}">
                                    {{ $type->name }} ({{ $type->code }}) - 
                                    вартість: {{ number_format($type->default_premium, 2) }} грн - 
                                    термін: {{ $type->duration_months }} міс.
                                    @if($type->franchise_value > 0)
                                        - франшиза: {{ $type->franchise_value }} 
                                        ({{ $type->franchise_type == 'fixed' ? 'грн' : '%' }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('policy_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Номер полісу</label>
                        <input type="text" name="policy_number" class="form-control @error('policy_number') is-invalid @enderror" placeholder="Номер полісу" required>
                        @error('policy_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Дата початку страхування</label>
                        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Дата закінчення страхування (розраховується автоматично)</label>
                        <input type="date" id="end_date" class="form-control" readonly style="background-color: #e9ecef;">
                        <small class="text-muted">Автоматично з типу полісу: тривалість {{ $policyTypes->first()->duration_months ?? '?' }} міс.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-info">
                            <strong>💰 Вартість полісу:</strong> 
                            <span id="premium_display">0.00</span> грн
                            <input type="hidden" name="premium" id="premium">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-plus"></i> Додати поліс
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card">
        <div class="card-header">
            <h4>Список полісів</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Клієнт</th>
                            <th>Тип полісу</th>
                            <th>Номер полісу</th>
                            <th>Початок</th>
                            <th>Закінчення</th>
                            <th>Вартість полісу</th>
                            <th>Франшиза</th>
                            <th>Статус</th>
                            <th>Дії</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($policies as $policy)
                        <tr>
                            <td>{{ $policy->id }}</td>
                            <td>
                                <strong>{{ $policy->client->last_name ?? 'Немає' }} {{ $policy->client->first_name ?? '' }}</strong><br>
                                <small class="text-muted">📞 {{ $policy->client->phone ?? 'Немає' }}</small>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $policy->policyType->name ?? 'Немає' }}</span><br>
                                <small class="text-muted">{{ $policy->policyType->code ?? '' }}</small>
                                <br><small>📅 Термін: {{ $policy->policyType->duration_months ?? 0 }} міс.</small>
                            </td>
                            <td>
                                <code>{{ $policy->policy_number }}</code>
                            </td>
                            <td>{{ $policy->start_date->format('d.m.Y') }}</td>
                            <td>{{ $policy->end_date->format('d.m.Y') }}</td>
                            <td>
                                <strong class="text-success">{{ number_format($policy->premium, 2) }}</strong> грн
                            </td>
                            <td>
                                @if($policy->policyType->franchise_value > 0)
                                    <span class="badge badge-warning">
                                        {{ $policy->policyType->franchise_value }}
                                        {{ $policy->policyType->franchise_type == 'fixed' ? 'грн' : '%' }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary">Без франшизи</span>
                                @endif
                            </td>
                            <td>
                                @if($policy->status == 'active')
                                    <span class="badge badge-success">✅ Активний</span>
                                @elseif($policy->status == 'expired')
                                    <span class="badge badge-danger">⏰ Прострочений</span>
                                @else
                                    <span class="badge badge-warning">❌ Скасований</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-warning" data-toggle="modal" data-target="#editModal{{ $policy->id }}" title="Редагувати">
                                        ✏️
                                    </button>
                                    <form method="POST" action="{{ route('manager.policies.destroy', $policy) }}" style="display:inline;" onsubmit="return confirm('Ви впевнені, що хочете видалити цей поліс?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" title="Видалити">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- EDIT MODAL --}}
                        <div class="modal fade" id="editModal{{ $policy->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Редагувати поліс №{{ $policy->policy_number }}</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form method="POST" action="{{ route('manager.policies.update', $policy) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Клієнт</label>
                                                        <select name="client_id" class="form-control" required>
                                                            @foreach($clients as $client)
                                                                <option value="{{ $client->id }}" {{ $policy->client_id == $client->id ? 'selected' : '' }}>
                                                                    {{ $client->last_name }} {{ $client->first_name }} (тел: {{ $client->phone }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Тип полісу</label>
                                                        <select name="policy_type_id" class="form-control policy-type-edit" data-policy-id="{{ $policy->id }}" required>
                                                            @foreach($policyTypes as $type)
                                                                <option value="{{ $type->id }}" {{ $policy->policy_type_id == $type->id ? 'selected' : '' }}
                                                                    data-premium="{{ $type->default_premium }}"
                                                                    data-duration="{{ $type->duration_months }}">
                                                                    {{ $type->name }} ({{ $type->code }}) - 
                                                                    {{ number_format($type->default_premium, 2) }} грн - 
                                                                    {{ $type->duration_months }} міс.
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
                                                        <input type="text" name="policy_number" value="{{ $policy->policy_number }}" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Статус</label>
                                                        <select name="status" class="form-control">
                                                            <option value="active" {{ $policy->status == 'active' ? 'selected' : '' }}>Активний</option>
                                                            <option value="expired" {{ $policy->status == 'expired' ? 'selected' : '' }}>Прострочений</option>
                                                            <option value="cancelled" {{ $policy->status == 'cancelled' ? 'selected' : '' }}>Скасований</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Дата початку</label>
                                                        <input type="date" name="start_date" value="{{ $policy->start_date->format('Y-m-d') }}" class="form-control start-date-edit" data-policy-id="{{ $policy->id }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Дата закінчення (розраховується автоматично)</label>
                                                        <input type="date" id="edit_end_date_{{ $policy->id }}" class="form-control" readonly style="background-color: #e9ecef;">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="alert alert-info">
                                                        <strong>💰 Вартість полісу:</strong> 
                                                        <span id="edit_premium_display_{{ $policy->id }}">{{ number_format($policy->premium, 2) }}</span> грн
                                                        <input type="hidden" name="premium" id="edit_premium_{{ $policy->id }}" value="{{ $policy->premium }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Скасувати</button>
                                            <button type="submit" class="btn btn-primary">Зберегти зміни</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">
                                <div class="alert alert-info mb-0">
                                    Немає жодного полісу
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- КОМПАКТНА ПАГІНАЦІЯ - МАЛЕНЬКІ СТРІЛКИ --}}
            @if($policies->hasPages())
            <div class="mt-3 d-flex justify-content-center">
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        {{-- Previous Page Link --}}
                        @if ($policies->onFirstPage())
                            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $policies->previousPageUrl() }}" rel="prev">&laquo;</a></li>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($policies->getUrlRange(1, $policies->lastPage()) as $page => $url)
                            @if ($page == $policies->currentPage())
                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($policies->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $policies->nextPageUrl() }}" rel="next">&raquo;</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                        @endif
                    </ul>
                </nav>
            </div>
            @endif
            {{-- КІНЕЦЬ КОМПАКТНОЇ ПАГІНАЦІЇ --}}
            
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Функция расчета даты окончания
        function calculateEndDate(startDate, durationMonths) {
            if (!startDate || !durationMonths) return '';
            const date = new Date(startDate);
            if (isNaN(date.getTime())) return '';
            date.setMonth(date.getMonth() + parseInt(durationMonths));
            return date.toISOString().split('T')[0];
        }
        
        // ========== ДЛЯ ФОРМЫ СОЗДАНИЯ ==========
        const policyTypeSelect = document.getElementById('policy_type_id');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const premiumDisplay = document.getElementById('premium_display');
        const premiumHidden = document.getElementById('premium');
        
        function updateCreateForm() {
            const selectedOption = policyTypeSelect.options[policyTypeSelect.selectedIndex];
            if (!selectedOption.value) return;
            
            const premium = selectedOption.dataset.premium;
            const durationMonths = selectedOption.dataset.duration;
            
            // Обновляем стоимость
            if (premium) {
                premiumDisplay.textContent = parseFloat(premium).toFixed(2);
                premiumHidden.value = premium;
            }
            
            // Обновляем дату окончания, если есть дата начала
            if (startDateInput.value && durationMonths) {
                const endDate = calculateEndDate(startDateInput.value, durationMonths);
                if (endDate) {
                    endDateInput.value = endDate;
                }
            }
        }
        
        if (policyTypeSelect) {
            policyTypeSelect.addEventListener('change', updateCreateForm);
        }
        
        if (startDateInput) {
            startDateInput.addEventListener('change', function() {
                const selectedOption = policyTypeSelect.options[policyTypeSelect.selectedIndex];
                const durationMonths = selectedOption.dataset.duration;
                if (this.value && durationMonths) {
                    const endDate = calculateEndDate(this.value, durationMonths);
                    if (endDate) {
                        endDateInput.value = endDate;
                    }
                }
            });
        }
        
        // Инициализация формы создания
        if (policyTypeSelect && policyTypeSelect.value) {
            updateCreateForm();
        }
        
        // ========== ДЛЯ МОДАЛЬНЫХ ОКОН РЕДАКТИРОВАНИЯ ==========
        document.querySelectorAll('.policy-type-edit').forEach(select => {
            const policyId = select.dataset.policyId;
            const endDateInput = document.getElementById(`edit_end_date_${policyId}`);
            const premiumDisplay = document.getElementById(`edit_premium_display_${policyId}`);
            const premiumHidden = document.getElementById(`edit_premium_${policyId}`);
            const startDateInput = document.querySelector(`.start-date-edit[data-policy-id="${policyId}"]`);
            
            function updateEditForm() {
                const selectedOption = select.options[select.selectedIndex];
                const premium = selectedOption.dataset.premium;
                const durationMonths = selectedOption.dataset.duration;
                
                // Обновляем стоимость
                if (premium) {
                    premiumDisplay.textContent = parseFloat(premium).toFixed(2);
                    premiumHidden.value = premium;
                }
                
                // Обновляем дату окончания
                if (startDateInput && startDateInput.value && durationMonths) {
                    const endDate = calculateEndDate(startDateInput.value, durationMonths);
                    if (endDate && endDateInput) {
                        endDateInput.value = endDate;
                    }
                }
            }
            
            select.addEventListener('change', updateEditForm);
            
            if (startDateInput) {
                startDateInput.addEventListener('change', function() {
                    const selectedOption = select.options[select.selectedIndex];
                    const durationMonths = selectedOption.dataset.duration;
                    if (this.value && durationMonths && endDateInput) {
                        const endDate = calculateEndDate(this.value, durationMonths);
                        if (endDate) {
                            endDateInput.value = endDate;
                        }
                    }
                });
            }
            
            // Инициализация при открытии модального окна
            $(select.closest('.modal')).on('shown.bs.modal', function() {
                updateEditForm();
            });
        });
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
    
    .table thead th {
        background-color: #343a40;
        color: white;
        vertical-align: middle;
    }
    
    .table tbody td {
        vertical-align: middle;
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
    
    code {
        background-color: #f4f4f4;
        padding: 2px 5px;
        border-radius: 3px;
    }
    
    /* Стилі для компактної пагінації */
    .pagination.pagination-sm {
        gap: 2px;
    }
    .pagination.pagination-sm .page-link {
        padding: 0.2rem 0.5rem;
        font-size: 0.7rem;
        border-radius: 3px;
    }
    .pagination.pagination-sm .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
    }
    .pagination.pagination-sm .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
        border-color: #dee2e6;
    }
</style>
@endpush