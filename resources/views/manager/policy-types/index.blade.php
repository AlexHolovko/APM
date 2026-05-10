@extends('adminlte::page')

@section('title', 'Типи полісів')

@section('content')

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h3>📋 Типи полісів</h3>
        </div>
    </div>

    {{-- CREATE --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">➕ Додати новий тип полісу</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('manager.policy-types.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-2">
                        <input type="text" name="name" class="form-control" placeholder="Назва" required>
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2">
                        <input type="text" name="code" class="form-control" placeholder="Код" required>
                    </div>
                    <div class="col-md-3 col-sm-12 mb-2">
                        <input type="text" name="description" class="form-control" placeholder="Опис">
                    </div>
                    <div class="col-md-2 col-sm-4 mb-2">
                        <input type="number" step="0.01" name="default_premium" class="form-control" placeholder="Вартість" required>
                    </div>
                    <div class="col-md-1 col-sm-4 mb-2">
                        <input type="number" name="duration_months" class="form-control" placeholder="Міс." required>
                    </div>
                    <div class="col-md-1 col-sm-4 mb-2">
                        <button class="btn btn-success w-100">Додати</button>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="number" step="0.01" name="franchise_value" class="form-control" placeholder="Франшиза" value="0">
                            <div class="input-group-append">
                                <select name="franchise_type" class="form-control" style="width: 80px;">
                                    <option value="fixed">грн</option>
                                    <option value="percentage">%</option>
                                </select>
                            </div>
                        </div>
                        <small class="text-muted">Сума, яку оплачує клієнт при настанні випадку (0 = без франшизи)</small>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- TABLE WITH RESPONSIVE WRAPPER --}}
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h4 class="mb-0">📊 Список типів полісів</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 5%">ID</th>
                            <th style="width: 12%">Назва</th>
                            <th style="width: 8%">Код</th>
                            <th style="width: 25%">Опис</th>
                            <th style="width: 10%">Вартість</th>
                            <th style="width: 10%">Франшиза</th>
                            <th style="width: 8%">Тривалість</th>
                            <th style="width: 8%">Статус</th>
                            <th style="width: 14%">Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($policyTypes as $type)
                        <tr>
                            <td>{{ $type->id }}</td>
                            <td>{{ $type->name }}</td>
                            <td>{{ $type->code }}</td>
                            <td>{{ $type->description ?? '—' }}</td>
                            <td>{{ number_format($type->default_premium, 2) }} грн</td>
                            <td>
                                @if($type->franchise_value > 0)
                                    <span class="badge badge-warning">
                                        {{ $type->franchise_value }}
                                        @if($type->franchise_type == 'percentage')
                                            %
                                        @else
                                            грн
                                        @endif
                                    </span>
                                @else
                                    <span class="badge badge-secondary">Без франшизи</span>
                                @endif
                             </td>
                            <td>{{ $type->duration_months }} міс.</td>
                            <td>
                                @if($type->is_active)
                                    <span class="badge badge-success">Активний</span>
                                @else
                                    <span class="badge badge-danger">Неактивний</span>
                                @endif
                             </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-warning" 
                                            data-toggle="modal" 
                                            data-target="#edit{{ $type->id }}">
                                        <i class="fas fa-edit"></i> ✏️
                                    </button>
                                    
                                    <form method="POST" 
                                          action="{{ route('manager.policy-types.destroy', $type) }}"
                                          style="display:inline;"
                                          onsubmit="return confirm('Ви впевнені, що хочете видалити цей тип полісу?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" type="submit">
                                            <i class="fas fa-trash"></i> 🗑
                                        </button>
                                    </form>
                                </div>
                             </td>
                         </tr>

                        {{-- EDIT MODAL --}}
                        <div class="modal fade" id="edit{{ $type->id }}">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-warning">
                                        <h5 class="modal-title">Редагувати тип полісу: {{ $type->name }}</h5>
                                        <button type="button" class="close" data-dismiss="modal">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <form method="POST" action="{{ route('manager.policy-types.update', $type) }}">
                                        @csrf
                                        @method('PUT')
                                        
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Назва <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" value="{{ old('name', $type->name) }}" 
                                                               class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Код <span class="text-danger">*</span></label>
                                                        <input type="text" name="code" value="{{ old('code', $type->code) }}" 
                                                               class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Опис</label>
                                                <textarea name="description" class="form-control" rows="2">{{ old('description', $type->description) }}</textarea>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Вартість (грн) <span class="text-danger">*</span></label>
                                                        <input type="number" step="0.01" name="default_premium" 
                                                               value="{{ old('default_premium', $type->default_premium) }}" 
                                                               class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Тривалість (місяців) <span class="text-danger">*</span></label>
                                                        <input type="number" name="duration_months" 
                                                               value="{{ old('duration_months', $type->duration_months) }}" 
                                                               class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Статус</label>
                                                        <select name="is_active" class="form-control">
                                                            <option value="1" {{ old('is_active', $type->is_active) ? 'selected' : '' }}>Активний</option>
                                                            <option value="0" {{ old('is_active', !$type->is_active) ? 'selected' : '' }}>Неактивний</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row mt-2">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Франшиза</label>
                                                        <div class="input-group">
                                                            <input type="number" step="0.01" name="franchise_value" 
                                                                   value="{{ old('franchise_value', $type->franchise_value) }}" 
                                                                   class="form-control">
                                                            <div class="input-group-append">
                                                                <select name="franchise_type" class="form-control">
                                                                    <option value="fixed" {{ old('franchise_type', $type->franchise_type) == 'fixed' ? 'selected' : '' }}>грн</option>
                                                                    <option value="percentage" {{ old('franchise_type', $type->franchise_type) == 'percentage' ? 'selected' : '' }}>%</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <small class="text-muted">0 = без франшизи</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Скасувати</button>
                                            <button type="submit" class="btn btn-success">Зберегти</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                <i class="fas fa-database fa-2x mb-2 d-block"></i>
                                Немає жодного типу полісу. Додайте перший!
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Пагінація --}}
            @if(isset($policyTypes) && method_exists($policyTypes, 'links'))
                <div class="d-flex justify-content-center mt-3">
                    {{ $policyTypes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

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
    
    .input-group {
        flex-wrap: nowrap;
    }
    
    .card-header h4 {
        margin: 0;
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

@push('js')
<script>
    // Автоматичне закриття модальних вікон після помилок валідації
    @if($errors->any())
        @if(old('_method') == 'PUT')
            $(document).ready(function() {
                $('#edit{{ old('id') }}').modal('show');
            });
        @endif
    @endif
</script>
@endpush