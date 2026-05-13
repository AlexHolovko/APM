@extends('adminlte::page')

@section('title', 'Аудит системи')

@section('content_header')
    <h1><i class="fas fa-history"></i> Аудит системи</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Журнал дій користувачів</h3>
        <div class="card-tools">
            <form method="GET" class="form-inline">
                <input type="date" name="date_from" class="form-control form-control-sm mr-2" value="{{ request('date_from') }}">
                <input type="date" name="date_to" class="form-control form-control-sm mr-2" value="{{ request('date_to') }}">
                <select name="user_id" class="form-control form-control-sm mr-2">
                    <option value="">Всі користувачі</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                <select name="action" class="form-control form-control-sm mr-2">
                    <option value="">Всі дії</option>
                    <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Вхід</option>
                    <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Вихід</option>
                    <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Створення</option>
                    <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Оновлення</option>
                    <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Видалення</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter"></i> Фільтр
                </button>
                <a href="{{ route('admin.audit.index') }}" class="btn btn-default btn-sm ml-2">
                    <i class="fas fa-sync"></i> Скинути
                </a>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Користувач</th>
                        <th style="width: 120px;">Дія</th>
                        <th style="width: 130px;">IP адреса</th>
                        <th style="width: 160px;">Час</th>
                        <th style="width: 70px;">Деталі</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>
                            <strong>{{ $log->user ? $log->user->name : 'Система' }}</strong>
                            @if($log->user && $log->user->email)
                                <br><small class="text-muted">{{ $log->user->email }}</small>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeColor = 'secondary';
                                if (str_contains($log->action, 'create')) $badgeColor = 'success';
                                elseif (str_contains($log->action, 'update')) $badgeColor = 'info';
                                elseif (str_contains($log->action, 'delete')) $badgeColor = 'danger';
                                elseif ($log->action == 'login') $badgeColor = 'primary';
                                elseif ($log->action == 'logout') $badgeColor = 'warning';
                            @endphp
                            <span class="badge badge-{{ $badgeColor }}">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </td>
                        <td><code>{{ $log->ip_address ?? '-' }}</code></td>
                        <td>{{ $log->created_at ? $log->created_at->format('d.m.Y H:i:s') : '-' }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" onclick="showDetails({{ $log->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i> Немає записів аудиту
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- КОМПАКТНА ПАГІНАЦІЯ - малі стрілки -->
        <div class="p-2 d-flex justify-content-center">
            @if($logs->hasPages())
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        {{-- Previous Page Link --}}
                        @if ($logs->onFirstPage())
                            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $logs->previousPageUrl() }}" rel="prev">&laquo;</a></li>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
                            @if ($page == $logs->currentPage())
                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($logs->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $logs->nextPageUrl() }}" rel="next">&raquo;</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
        <!-- КІНЕЦЬ КОМПАКТНОЇ ПАГІНАЦІЇ -->
        
    </div>
</div>

<!-- Modal Details -->
<div class="modal fade" id="detailsModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Деталі дії
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="details-content">
                <div class="text-center p-5">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Завантаження...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function showDetails(id) {
        // Показываем модальное окно
        $('#detailsModal').modal('show');
        $('#details-content').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Завантаження...</p></div>');
        
        // Загружаем данные
        $.ajax({
            url: '/admin/audit/' + id,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.error) {
                    $('#details-content').html('<div class="alert alert-danger">' + data.error + '</div>');
                    return;
                }
                
                // Форматируем детали
                let detailsHtml = '';
                if (data.details) {
                    if (typeof data.details === 'object') {
                        detailsHtml = '<pre class="bg-light p-3" style="border-radius: 5px; overflow-x: auto; max-height: 300px;"><code>' + JSON.stringify(data.details, null, 2) + '</code></pre>';
                    } else if (typeof data.details === 'string' && data.details.length > 0) {
                        detailsHtml = '<div class="alert alert-info">' + data.details + '</div>';
                    } else {
                        detailsHtml = '<div class="alert alert-secondary">Немає додаткових даних</div>';
                    }
                } else {
                    detailsHtml = '<div class="alert alert-secondary">Немає додаткових даних</div>';
                }
                
                let html = `
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width: 150px; background-color: #f8f9fa;">ID запису:</th>
                            <td><code>${data.id}</code></td>
                        </tr>
                        <tr>
                            <th style="background-color: #f8f9fa;">Користувач:</th>
                            <td>
                                <strong>${data.user_name}</strong>
                                ${data.user_email && data.user_email !== '-' ? `<br><small class="text-muted">${data.user_email}</small>` : ''}
                            </td>
                        </tr>
                        <tr>
                            <th style="background-color: #f8f9fa;">Дія:</th>
                            <td>
                                <span class="badge badge-info">${data.action}</span>
                                <br><small class="text-muted">Код: ${data.action_code}</small>
                            </td>
                        </tr>
                        <tr>
                            <th style="background-color: #f8f9fa;">IP адреса:</th>
                            <td><code>${data.ip_address}</code></td>
                        </tr>
                        <tr>
                            <th style="background-color: #f8f9fa;">Час:</th>
                            <td>${data.created_at}</td>
                        </tr>
                        <tr>
                            <th style="background-color: #f8f9fa;">User Agent:</th>
                            <td><small class="text-muted">${data.user_agent}</small></td>
                        </tr>
                        <tr>
                            <th style="background-color: #f8f9fa;">Модель:</th>
                            <td>${data.model_type || '-'} ${data.model_id ? '(ID: ' + data.model_id + ')' : ''}</td>
                        </tr>
                        <tr>
                            <th style="background-color: #f8f9fa;">Деталі:</th>
                            <td>${detailsHtml}</td>
                        </tr>
                    </table>
                `;
                
                $('#details-content').html(html);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                $('#details-content').html('<div class="alert alert-danger">Помилка завантаження даних: ' + error + '</div>');
            }
        });
    }
</script>
@endpush

@push('css')
<style>
    .modal-lg {
        max-width: 800px;
    }
    pre {
        font-size: 12px;
    }
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
</style>
@endpush