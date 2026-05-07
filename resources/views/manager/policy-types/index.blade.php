@extends('adminlte::page')

@section('title', 'Типи полісів')

@section('content')

<div class="container-fluid">
    <h3>Типи полісів</h3>

    {{-- CREATE --}}
    <div class="card mb-3">
        <div class="card-header">
            <h4>Додати новий тип полісу</h4>
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

            </form>
        </div>
    </div>

    {{-- TABLE WITH RESPONSIVE WRAPPER --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 5%">ID</th>
                            <th style="width: 15%">Назва</th>
                            <th style="width: 10%">Код</th>
                            <th style="width: 30%">Опис</th>
                            <th style="width: 12%">Вартість</th>
                            <th style="width: 10%">Тривалість</th>
                            <th style="width: 8%">Статус</th>
                            <th style="width: 10%">Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($policyTypes as $type)
                        <tr>
                            <td>{{ $type->id }}</td>
                            <td>{{ $type->name }}</td>
                            <td>{{ $type->code }}</td>
                            <td>{{ $type->description ?? '—' }}</td>
                            <td>{{ number_format($type->default_premium, 2) }} грн</td>
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
                        <div class="modal fade" id="edit{{ $type->id }}">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
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
                                                        <label>Назва</label>
                                                        <input type="text" name="name" value="{{ $type->name }}" 
                                                               class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Код</label>
                                                        <input type="text" name="code" value="{{ $type->code }}" 
                                                               class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Опис</label>
                                                <textarea name="description" class="form-control" rows="2">{{ $type->description }}</textarea>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Вартість (грн)</label>
                                                        <input type="number" step="0.01" name="default_premium" 
                                                               value="{{ $type->default_premium }}" 
                                                               class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Тривалість (місяців)</label>
                                                        <input type="number" name="duration_months" 
                                                               value="{{ $type->duration_months }}" 
                                                               class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Статус</label>
                                                <select name="is_active" class="form-control">
                                                    <option value="1" {{ $type->is_active ? 'selected' : '' }}>Активний</option>
                                                    <option value="0" {{ !$type->is_active ? 'selected' : '' }}>Неактивний</option>
                                                </select>
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
                    @endforeach
                    </tbody>
                </table>
            </div>
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