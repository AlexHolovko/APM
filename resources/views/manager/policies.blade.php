@extends('adminlte::page')

@section('title', 'Поліси')

@section('content')

<div class="container-fluid">

    <h3><i class="fas fa-file-contract"></i> Страхові поліси</h3>

    {{-- 🔍 ФІЛЬТР --}}
    <form method="GET" class="row mb-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control"
                   placeholder="Номер полісу або ПІБ клієнта"
                   value="{{ request('search') }}">
        </div>

        <div class="col-md-3">
            <select name="status" class="form-control">
                <option value="">Всі статуси</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активні</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Чернетки</option>
                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Прострочені</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Скасовані</option>
            </select>
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary btn-block">Пошук</button>
        </div>

        <div class="col-md-3">
            <a href="{{ route('manager.policies.create') }}" class="btn btn-success btn-block">
                <i class="fas fa-plus"></i> Додати новий поліс
            </a>
        </div>
    </form>

    {{-- 📊 ТАБЛИЦЯ --}}
    <div class="card">
        <div class="card-body">

            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Номер полісу</th>
                        <th>Клієнт</th>
                        <th>Тип</th>
                        <th>Період дії</th>
                        <th class="text-right">Премія</th>
                        <th>Статус</th>
                        <th width="160">Дії</th>
                    </tr>
                </thead>
                <tbody>

                @forelse($policies as $policy)
                    <tr>
                        <td><strong>{{ $policy->policy_number }}</strong></td>
                        <td>
                            {{ $policy->client->last_name ?? '' }} 
                            {{ $policy->client->first_name ?? '' }} 
                            {{ $policy->client->middle_name ?? '' }}
                        </td>
                        <td>{{ $policy->policy_type ? ucfirst($policy->policy_type) : '—' }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($policy->start_date)->format('d.m.Y') }} — 
                            {{ \Carbon\Carbon::parse($policy->end_date)->format('d.m.Y') }}
                        </td>
                        <td class="text-right fw-bold">{{ number_format($policy->premium, 2) }} грн</td>
                        <td>
                            @if($policy->status == 'active')
                                <span class="badge bg-success">Активний</span>
                            @elseif($policy->status == 'draft')
                                <span class="badge bg-warning">Чернетка</span>
                            @elseif($policy->status == 'expired')
                                <span class="badge bg-secondary">Прострочений</span>
                            @else
                                <span class="badge bg-danger">{{ ucfirst($policy->status) }}</span>
                            @endif
                        </td>
                        <td>

                            {{-- ✏️ РЕДАГУВАННЯ (Modal) --}}
                            <button class="btn btn-warning btn-sm"
                                    data-toggle="modal"
                                    data-target="#editPolicyModal{{ $policy->id }}">
                                ✏️
                            </button>

                            {{-- 👁 Перегляд --}}
                            <a href="{{ route('manager.policies.show', $policy) }}" 
                               class="btn btn-info btn-sm">
                                👁
                            </a>

                            {{-- 🗑 ВИДАЛЕННЯ --}}
                            <form method="POST" 
                                  action="{{ route('manager.policies.destroy', $policy) }}"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Видалити поліс №{{ $policy->policy_number }}?')">
                                    🗑
                                </button>
                            </form>

                        </td>
                    </tr>

                    {{-- ================= MODAL РЕДАГУВАННЯ ================= --}}
                    <div class="modal fade" id="editPolicyModal{{ $policy->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        Редагування полісу №{{ $policy->policy_number }}
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal">×</button>
                                </div>

                                <form method="POST" action="{{ route('manager.policies.update', $policy) }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="modal-body">

                                        <!-- Клієнт -->
                                        <div class="form-group">
                                            <label>Клієнт</label>
                                            <select name="client_id" class="form-control" required>
                                                @foreach($clients as $client)
                                                    <option value="{{ $client->id }}" 
                                                        {{ $policy->client_id == $client->id ? 'selected' : '' }}>
                                                        {{ $client->last_name }} {{ $client->first_name }} {{ $client->middle_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Номер полісу -->
                                        <div class="form-group">
                                            <label>Номер полісу</label>
                                            <input type="text" name="policy_number" 
                                                   class="form-control" 
                                                   value="{{ $policy->policy_number }}" required>
                                        </div>

                                        <!-- Тип полісу -->
                                        <div class="form-group">
                                            <label>Тип страхування</label>
                                            <select name="policy_type" class="form-control">
                                                <option value="auto" {{ $policy->policy_type == 'auto' ? 'selected' : '' }}>Автострахування</option>
                                                <option value="health" {{ $policy->policy_type == 'health' ? 'selected' : '' }}>Медичне</option>
                                                <option value="property" {{ $policy->policy_type == 'property' ? 'selected' : '' }}>Майнове</option>
                                                <option value="life" {{ $policy->policy_type == 'life' ? 'selected' : '' }}>Страхування життя</option>
                                                <option value="travel" {{ $policy->policy_type == 'travel' ? 'selected' : '' }}>Туристичне</option>
                                            </select>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Дата початку</label>
                                                    <input type="date" name="start_date" 
                                                           class="form-control" 
                                                           value="{{ $policy->start_date }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Дата закінчення</label>
                                                    <input type="date" name="end_date" 
                                                           class="form-control" 
                                                           value="{{ $policy->end_date }}" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Страхова премія (грн)</label>
                                            <input type="number" step="0.01" name="premium" 
                                                   class="form-control" 
                                                   value="{{ $policy->premium }}" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Статус</label>
                                            <select name="status" class="form-control">
                                                <option value="draft" {{ $policy->status == 'draft' ? 'selected' : '' }}>Чернетка</option>
                                                <option value="active" {{ $policy->status == 'active' ? 'selected' : '' }}>Активний</option>
                                                <option value="expired" {{ $policy->status == 'expired' ? 'selected' : '' }}>Прострочений</option>
                                                <option value="cancelled" {{ $policy->status == 'cancelled' ? 'selected' : '' }}>Скасований</option>
                                            </select>
                                        </div>

                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрити</button>
                                        <button type="submit" class="btn btn-success">Зберегти зміни</button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                    {{-- ================= /MODAL ================= --}}

                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">Полісів не знайдено</td>
                    </tr>
                @endforelse

                </tbody>
            </table>

            {{-- Пагінація --}}
            {{ $policies->withQueryString()->links() }}

        </div>
    </div>

</div>

@endsection