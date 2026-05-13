@extends('adminlte::page')

@section('title', 'Прострочені платежі')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-exclamation-triangle"></i> Прострочені платежі</h1>
        <a href="{{ route('accountant.dashboard') }}" class="btn btn-default btn-sm">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Загальна сума прострочених платежів</span>
                <span class="info-box-number">{{ number_format($stats['total_pending'] ?? 0, 2) }} грн</span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-calendar-times"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Прострочено (термін минув)</span>
                <span class="info-box-number">{{ number_format($stats['total_overdue'] ?? 0, 2) }} грн</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Список прострочених платежів</h3>
        <div class="card-tools">
            <form method="GET" class="form-inline">
                <input type="text" name="search" class="form-control form-control-sm mr-2" 
                       placeholder="Пошук..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i> Пошук
                </button>
                @if(request('search'))
                    <a href="{{ route('accountant.payments') }}" class="btn btn-default btn-sm ml-2">
                        <i class="fas fa-times"></i> Очистити
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Дата платежу</th>
                        <th>Номер полісу</th>
                        <th>Клієнт</th>
                        <th>Сума</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->id }}</td>
                        <td>{{ $payment->date ? \Carbon\Carbon::parse($payment->date)->format('d.m.Y') : '-' }}</td>
                        <td>{{ $payment->policy->policy_number ?? 'N/A' }}</td>
                        <td>
                            {{ $payment->policy->client->last_name ?? '' }} 
                            {{ $payment->policy->client->first_name ?? '' }}
                        </td>
                        <td><strong class="text-danger">{{ number_format($payment->amount, 2) }} грн</strong></td>
                        <td><span class="badge badge-warning">Прострочено</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i> Немає прострочених платежів
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ТОЛЬКО КАСТОМНАЯ ПАГИНАЦИЯ --}}
        @if($payments->total() > 0)
        <div class="my-pagination-container">
            @if($payments->hasPages())
            <div class="my-pagination">
                {{-- Кнопка "Первая" --}}
                @if(!$payments->onFirstPage())
                    <a href="{{ $payments->url(1) }}" class="my-page-link">«</a>
                @endif

                {{-- Кнопка "Назад" --}}
                @if($payments->currentPage() > 1)
                    <a href="{{ $payments->previousPageUrl() }}" class="my-page-link">‹</a>
                @endif

                {{-- Номера страниц --}}
                @php
                    $current = $payments->currentPage();
                    $last = $payments->lastPage();
                    $start = max(1, $current - 2);
                    $end = min($last, $current + 2);
                    
                    if ($start > 1) {
                        echo '<a href="'.$payments->url(1).'" class="my-page-link">1</a>';
                        if ($start > 2) echo '<span class="my-dots">...</span>';
                    }
                    
                    for ($i = $start; $i <= $end; $i++) {
                        if ($i == $current) {
                            echo '<span class="my-page-link active">'.$i.'</span>';
                        } else {
                            echo '<a href="'.$payments->url($i).'" class="my-page-link">'.$i.'</a>';
                        }
                    }
                    
                    if ($end < $last) {
                        if ($end < $last - 1) echo '<span class="my-dots">...</span>';
                        echo '<a href="'.$payments->url($last).'" class="my-page-link">'.$last.'</a>';
                    }
                @endphp

                {{-- Кнопка "Вперед" --}}
                @if($payments->hasMorePages())
                    <a href="{{ $payments->nextPageUrl() }}" class="my-page-link">›</a>
                @endif

                {{-- Кнопка "Последняя" --}}
                @if($payments->currentPage() < $payments->lastPage())
                    <a href="{{ $payments->url($payments->lastPage()) }}" class="my-page-link">»</a>
                @endif
            </div>
            <div class="my-pagination-info">
                Показано {{ $payments->firstItem() }} - {{ $payments->lastItem() }} 
                з {{ $payments->total() }} результатів
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection

@push('css')
<style>
    /* Полностью скрываем стандартную пагинацию AdminLTE/Laravel */
    nav[aria-label="Pagination"],
    nav[role="navigation"],
    .pagination,
    .pagination-wrapper,
    .dataTables_paginate,
    ul.pagination,
    div.pagination {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        height: 0 !important;
        min-height: 0 !important;
        max-height: 0 !important;
        overflow: hidden !important;
        position: absolute !important;
        pointer-events: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Стили для кастомной пагинации */
    .my-pagination-container {
        padding: 15px 20px;
        background: #f8f9fa;
        border-top: 1px solid #dee2e6;
        text-align: center;
    }
    
    .my-pagination {
        display: inline-flex;
        gap: 5px;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }
    
    .my-page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 10px;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        color: #007bff;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
        cursor: pointer;
    }
    
    .my-page-link:hover {
        background: #007bff;
        border-color: #007bff;
        color: white;
        text-decoration: none;
    }
    
    .my-page-link.active {
        background: #007bff;
        border-color: #007bff;
        color: white;
        cursor: default;
    }
    
    .my-dots {
        padding: 0 5px;
        color: #6c757d;
    }
    
    .my-pagination-info {
        font-size: 12px;
        color: #6c757d;
        text-align: center;
        padding-top: 8px;
    }
</style>
@endpush

@push('js')
<script>
    // Удаляем любую стандартную пагинацию после загрузки
    document.addEventListener('DOMContentLoaded', function() {
        // Находим и удаляем все стандартные элементы пагинации
        const selectors = [
            '.pagination',
            '.dataTables_paginate',
            'nav[aria-label="Pagination"]',
            'nav[role="navigation"]',
            '.pagination-wrapper'
        ];
        
        selectors.forEach(selector => {
            document.querySelectorAll(selector).forEach(el => {
                // Не удаляем нашу кастомную пагинацию
                if (!el.closest('.my-pagination-container')) {
                    el.remove();
                }
            });
        });
        
        // Также удаляем любые элементы с классом page-item
        document.querySelectorAll('.page-item, .page-link').forEach(el => {
            if (!el.closest('.my-pagination-container')) {
                el.remove();
            }
        });
    });
</script>
@endpush