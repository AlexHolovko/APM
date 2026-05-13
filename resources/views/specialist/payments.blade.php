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
                        <td>{{ $payment->policy->client->last_name ?? '' }} {{ $payment->policy->client->first_name ?? '' }}</td>
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

        {{-- КАСТОМНА ПАГІНАЦІЯ --}}
        @if($payments->hasPages() && $payments->lastPage() > 1)
        <div class="custom-pagination-wrapper">
            <div class="custom-pagination">
                {{-- PREVIOUS --}}
                @if($payments->onFirstPage())
                    <span class="custom-page disabled">←</span>
                @else
                    <a href="{{ $payments->previousPageUrl() }}" class="custom-page">←</a>
                @endif

                @php
                    $currentPage = $payments->currentPage();
                    $lastPage = $payments->lastPage();
                    $start = max(1, $currentPage - 2);
                    $end = min($lastPage, $currentPage + 2);
                @endphp

                {{-- FIRST PAGE --}}
                @if($start > 1)
                    <a href="{{ $payments->url(1) }}" class="custom-page">1</a>
                    @if($start > 2)
                        <span class="custom-dots">…</span>
                    @endif
                @endif

                {{-- PAGES --}}
                @for($i = $start; $i <= $end; $i++)
                    @if($i == $currentPage)
                        <span class="custom-page active">{{ $i }}</span>
                    @else
                        <a href="{{ $payments->url($i) }}" class="custom-page">{{ $i }}</a>
                    @endif
                @endfor

                {{-- LAST PAGE --}}
                @if($end < $lastPage)
                    @if($end < $lastPage - 1)
                        <span class="custom-dots">…</span>
                    @endif
                    <a href="{{ $payments->url($lastPage) }}" class="custom-page">{{ $lastPage }}</a>
                @endif

                {{-- NEXT --}}
                @if($payments->hasMorePages())
                    <a href="{{ $payments->nextPageUrl() }}" class="custom-page">→</a>
                @else
                    <span class="custom-page disabled">→</span>
                @endif
            </div>
        </div>
        <div class="custom-pagination-info">
            Показано {{ $payments->firstItem() ?? 0 }} - {{ $payments->lastItem() ?? 0 }}
            з {{ $payments->total() ?? 0 }} результатів
        </div>
        @endif
    </div>
</div>
@endsection

@push('css')
<style>
/* Скрываем все возможные варианты стандартной пагинации Laravel */
.pagination,
.pagination-wrapper,
.pagination-container,
nav[aria-label="Pagination"],
nav[role="navigation"],
[class*="paginator"],
[class*="pagination"],
.dataTables_paginate,
.dataTables_paginate *,
ul.pagination,
div.pagination,
span.pagination,
.pagination-sm,
.pagination-lg,
.page-item,
.page-link,
[rel="prev"],
[rel="next"],
[role="navigation"]:not(.custom-pagination) {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
    height: 0 !important;
    min-height: 0 !important;
    max-height: 0 !important;
    overflow: hidden !important;
    position: absolute !important;
    z-index: -9999 !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
}

/* Кастомная пагинация */
.custom-pagination-wrapper {
    text-align: center;
    padding: 15px 10px;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    position: relative !important;
    height: auto !important;
}

.custom-pagination {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    flex-wrap: wrap;
}

.custom-page {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    min-width: 32px !important;
    height: 32px !important;
    padding: 0 8px !important;
    margin: 0 !important;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    background: #ffffff !important;
    color: #007bff !important;
    text-decoration: none !important;
    font-size: 14px !important;
    font-weight: normal !important;
    line-height: 1 !important;
    cursor: pointer;
    transition: all 0.2s ease;
}

.custom-page:hover {
    background: #007bff !important;
    color: #ffffff !important;
    border-color: #007bff !important;
    text-decoration: none !important;
}

.custom-page.active {
    background: #007bff !important;
    color: #ffffff !important;
    font-weight: bold !important;
    border-color: #007bff !important;
    cursor: default;
}

.custom-page.disabled {
    background: #e9ecef !important;
    color: #6c757d !important;
    cursor: not-allowed !important;
    opacity: 0.6;
}

.custom-dots {
    display: inline-flex;
    padding: 0 4px;
    color: #6c757d;
    font-size: 14px;
}

.custom-pagination-info {
    text-align: center;
    padding: 8px;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    color: #6c757d;
    font-size: 12px;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .custom-page {
        min-width: 28px !important;
        height: 28px !important;
        font-size: 12px !important;
    }
    .custom-pagination {
        gap: 3px;
    }
}
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Удаляем все возможные элементы стандартной пагинации
    const paginationSelectors = [
        '.pagination',
        '.pagination-wrapper',
        '[aria-label="Pagination"]',
        '[role="navigation"]',
        '.dataTables_paginate',
        'ul.pagination',
        'div.pagination'
    ];
    
    paginationSelectors.forEach(selector => {
        const elements = document.querySelectorAll(selector);
        elements.forEach(el => {
            if (!el.closest('.custom-pagination-wrapper')) {
                el.remove();
            }
        });
    });
});
</script>
@endpush