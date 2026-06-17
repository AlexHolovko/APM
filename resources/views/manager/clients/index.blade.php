@extends('adminlte::page')

@section('title', 'Клієнти')

@section('content_header')
    <h1><i class="fas fa-users"></i> Клієнти CRM</h1>
@stop

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Список клієнтів</h3>
        <div class="card-tools">
            <form method="GET" class="form-inline">
                <input type="text" name="name" class="form-control form-control-sm mr-2"
                       placeholder="Пошук по імені" value="{{ request('name') }}">
                <input type="text" name="phone" class="form-control form-control-sm mr-2"
                       placeholder="Телефон" value="{{ request('phone') }}">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i> Пошук
                </button>
                <a href="{{ route('manager.clients.index') }}" class="btn btn-default btn-sm ml-2">
                    <i class="fas fa-sync"></i> Скинути
                </a>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ПІБ</th>
                        <th>Телефон</th>
                        <th>Email</th>
                        <th>Місто</th>
                        <th width="100">Дії</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($clients as $client)
                    <tr>
                        <td>
                            {{ $client->last_name }} {{ $client->first_name }} {{ $client->middle_name }}
                        </td>

                        <td>{{ $client->phone ?? '—' }}</td>
                        <td>{{ $client->email ?? '—' }}</td>
                        <td>{{ $client->city ?? '—' }}</td>

                        <td style="white-space: nowrap;">
                            {{-- ✏️ EDIT BUTTON --}}
                            <button class="btn btn-warning btn-xs"
                                    data-toggle="modal"
                                    data-target="#editClientModal{{ $client->id }}">
                                <i class="fas fa-edit"></i>
                            </button>

                            {{-- 🗑 DELETE --}}
                            <form method="POST"
                                  action="{{ route('manager.clients.destroy', $client) }}"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-xs"
                                        onclick="return confirm('Видалити клієнта?')">
                                    <i class="fas fa-trash"></i>
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
                                        <i class="fas fa-edit"></i> Редагування клієнта
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
                                        <button type="button" class="btn btn-default" data-dismiss="modal">
                                            <i class="fas fa-times"></i> Скасувати
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save"></i> Зберегти
                                        </button>
                                    </div>

                                </form>

                            </div>
                        </div>
                    </div>
                    {{-- ================= /MODAL ================= --}}

                    @empty
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i> Немає клієнтів
                            </div>
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- КОМПАКТНА ПАГІНАЦІЯ - МАЛІ СТРІЛКИ (як в аудиті) --}}
        @if($clients->hasPages())
        <div class="p-2 d-flex justify-content-center">
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                    {{-- Previous Page Link --}}
                    @if ($clients->onFirstPage())
                        <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $clients->previousPageUrl() }}" rel="prev">&laquo;</a></li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($clients->getUrlRange(1, $clients->lastPage()) as $page => $url)
                        @if ($page == $clients->currentPage())
                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($clients->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $clients->nextPageUrl() }}" rel="next">&raquo;</a></li>
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

@endsection

@push('css')
<style>
    .table th {
        background-color: #f8f9fa;
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
    
    /* Стилі для маленьких кнопок дій */
    .btn-xs {
        padding: 0.1rem 0.3rem;
        font-size: 0.7rem;
        line-height: 1.2;
        border-radius: 0.2rem;
    }
</style>
@endpush