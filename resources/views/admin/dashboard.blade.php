@extends('adminlte::page')

@section('title', 'Панель адміністратора')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-tachometer-alt"></i> Панель адміністратора</h1>
        <span class="badge badge-info">{{ now()->format('d.m.Y H:i') }}</span>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Статистика -->
    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalUsers ?? 0 }}</h3>
                    <p>Користувачів системи</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                    Управління <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $totalRoles ?? 0 }}</h3>
                    <p>Ролей в системі</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-tag"></i>
                </div>
                <a href="{{ route('admin.roles.index') }}" class="small-box-footer">
                    Керувати ролями <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalAuditLogs ?? 0 }}</h3>
                    <p>Дій в системі</p>
                </div>
                <div class="icon">
                    <i class="fas fa-history"></i>
                </div>
                <a href="{{ route('admin.audit.index') }}" class="small-box-footer">
                    Переглянути аудит <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Останні дії -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i> Останні дії користувачів
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.audit.index') }}" class="btn btn-primary btn-sm">
                            Всі записи <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Користувач</th>
                                    <th>Дія</th>
                                    <th>IP адреса</th>
                                    <th>Час</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs ?? [] as $log)
                                <tr>
                                    <td>
                                        <strong>{{ $log->user->name ?? 'Система' }}</strong><br>
                                        <small class="text-muted">{{ $log->user->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $log->action == 'create' ? 'success' : ($log->action == 'update' ? 'info' : 'danger') }}">
                                            {{ str_replace('_', ' ', $log->action) }}
                                        </span>
                                    </td>
                                    <td><code>{{ $log->ip_address ?? '-' }}</code></td>
                                    <td>{{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Немає дій</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection