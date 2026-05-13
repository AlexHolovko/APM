@extends('adminlte::page')

@section('title', 'Панель бухгалтера')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
  <h1><i class="fas fa-calculator"></i> Панель бухгалтера</h1>
  <span class="badge badge-info">{{ now()->format('d.m.Y H:i') }}</span>
</div>
@stop

@section('content')
  <div class="container-fluid">
    <!-- Статистика -->
    <div class="row">
      <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>{{ number_format($totalPayments ?? 0, 2) }} <small>грн</small></h3>
            <p>Надходження (всього)</p>
          </div>
          <div class="icon">
            <i class="fas fa-arrow-down"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
          <div class="inner">
            <h3>{{ number_format($totalPayouts ?? 0, 2) }} <small>грн</small></h3>
            <p>Виплати (всього)</p>
          </div>
          <div class="icon">
            <i class="fas fa-arrow-up"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>{{ number_format($pendingPayouts ?? 0, 2) }} <small>грн</small></h3>
            <p>Очікує виплати</p>
          </div>
          <div class="icon">
            <i class="fas fa-clock"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>{{ number_format(($totalPayments ?? 0) - ($totalPayouts ?? 0), 2) }} <small>грн</small></h3>
            <p>Прибуток</p>
          </div>
          <div class="icon">
            <i class="fas fa-chart-line"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Графік -->
    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-chart-line"></i> Динаміка платежів за {{ date('Y') }} рік
            </h3>
          </div>
          <div class="card-body">
            <canvas id="paymentsChart"
              style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-exclamation-triangle"></i> Прострочені платежі
            </h3>
          </div>
          <div class="card-body">
            <div class="text-center">
              <!-- Изменено: overduePaymentsCount вместо overduePoliciesCount -->
              <h1 class="display-4 text-warning">{{ $overduePaymentsCount ?? 0 }}</h1>
              <p>прострочених платежів</p> <!-- Изменен текст -->
              <a href="{{ route('accountant.payments') }}" class="btn btn-warning btn-sm">
                <i class="fas fa-eye"></i> Переглянути
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Останні платежі -->
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-history"></i> Останні операції
          </h3>
          <div class="card-tools">
            <a href="{{ route('accountant.payouts') }}" class="btn btn-primary btn-sm">
              Всі виплати <i class="fas fa-arrow-right"></i>
            </a>
            <a href="{{ route('accountant.payments') }}" class="btn btn-warning btn-sm ml-2">
              <i class="fas fa-exclamation-triangle"></i> Прострочені
            </a>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Дата</th>
                  <th>Тип</th>
                  <th>Поліс</th>
                  <th>Клієнт</th>
                  <th>Сума</th>
                  <th>Статус</th>
                  <th>Перегляд</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recentPayments ?? [] as $payment)
                  <tr>
                    <td>
                      @if($payment->date)
                        {{ \Carbon\Carbon::parse($payment->date)->format('d.m.Y') }}
                      @elseif($payment->created_at)
                        {{ \Carbon\Carbon::parse($payment->created_at)->format('d.m.Y') }}
                      @else
                        -
                      @endif
                    </td>
                    <td>
                      <span class="badge badge-{{ $payment->payment_type == 'premium' ? 'success' : 'danger' }}">
                        {{ $payment->payment_type == 'premium' ? 'Надходження' : 'Виплата' }}
                      </span>
                    </td>
                    <td>{{ $payment->policy->policy_number ?? 'N/A' }}</td>
                    <td>
                      {{ $payment->policy->client->last_name ?? '' }}
                      {{ $payment->policy->client->first_name ?? '' }}
                    </td>
                    <td><strong>{{ number_format($payment->amount, 2) }} грн</strong></td>
                    <td>
                      <span class="badge badge-{{ $payment->status == 'completed' ? 'success' : 'warning' }}">
                        {{ $payment->status == 'completed' ? 'Виконано' : 'Очікує' }}
                      </span>
                    </td>
                    <td>
                      <button type="button" class="btn btn-sm btn-info" onclick="showPaymentDetails({{ $payment->id }})">
                        <i class="fas fa-info-circle"></i> Деталі
                      </button>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center">Немає операцій</td>
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

  <!-- Модальне вікно для деталей -->
  <div class="modal fade" id="paymentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title"><i class="fas fa-info-circle"></i> Деталі операції</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body" id="paymentDetailsContent">
          <div class="text-center p-4">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Завантаження...</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Закрити</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('js')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const monthlyStats = @json($monthlyStats ?? []);
    const months = ['Січ', 'Лют', 'Бер', 'Кві', 'Тра', 'Чер', 'Лип', 'Сер', 'Вер', 'Жов', 'Лис', 'Гру'];

    const premiumsData = Array(12).fill(0);
    const payoutsData = Array(12).fill(0);

    if (monthlyStats && monthlyStats.length > 0) {
      monthlyStats.forEach(stat => {
        const monthIndex = parseInt(stat.month) - 1;
        if (monthIndex >= 0 && monthIndex < 12) {
          premiumsData[monthIndex] = parseFloat(stat.total_premiums) || 0;
          payoutsData[monthIndex] = parseFloat(stat.total_payouts) || 0;
        }
      });
    }

    const ctx = document.getElementById('paymentsChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: months,
        datasets: [
          {
            label: 'Надходження (грн)',
            data: premiumsData,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            fill: true,
            tension: 0.4
          },
          {
            label: 'Виплати (грн)',
            data: payoutsData,
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            fill: true,
            tension: 0.4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top' }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return value.toLocaleString() + ' грн';
              }
            }
          }
        }
      }
    });

    function showPaymentDetails(id) {
      $('#paymentDetailsModal').modal('show');
      $('#paymentDetailsContent').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Завантаження...</p></div>');

      $.ajax({
        url: '/accountant/payment/' + id,
        method: 'GET',
        dataType: 'json',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        success: function (data) {
          let html = `
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <tr><th width="40%" style="background-color: #f8f9fa;">ID операції:</th><td><code>#${data.id}</code></td></tr>
                            <tr><th style="background-color: #f8f9fa;">Дата операції:</th><td>${data.date}</td></tr>
                            <tr><th style="background-color: #f8f9fa;">Тип операції:</th><td><span class="badge ${data.type.includes('Надходження') ? 'badge-success' : 'badge-danger'}">${data.type}</span></td></tr>
                            <tr><th style="background-color: #f8f9fa;">Сума:</th><td class="text-success"><strong>${data.amount} грн</strong></td></tr>
                            <tr><th style="background-color: #f8f9fa;">Статус:</th><td><span class="badge badge-${data.status_class}">${data.status}</span></td></tr>
                            <tr><th style="background-color: #f8f9fa;">ID транзакції:</th><td><code>${data.transaction_id}</code></td></tr>
                            <tr><th style="background-color: #f8f9fa;">Номер полісу:</th><td><strong>${data.policy_number}</strong></td></tr>
                            <tr><th style="background-color: #f8f9fa;">Клієнт:</th><td>${data.client_name}</td></tr>
                            <tr><th style="background-color: #f8f9fa;">Телефон:</th><td>${data.client_phone}</td></tr>
                            <tr><th style="background-color: #f8f9fa;">Email:</th><td>${data.client_email}</td></tr>
                            <tr><th style="background-color: #f8f9fa;">Опис:</th><td>${data.description}</td></tr>
                            <tr><th style="background-color: #f8f9fa;">Дата створення:</th><td>${data.created_at}</td></tr>
                        </table>
                    </div>
                `;
          $('#paymentDetailsContent').html(html);
        },
        error: function (xhr, status, error) {
          let errorMsg = 'Помилка завантаження даних';
          if (xhr.responseJSON && xhr.responseJSON.error) {
            errorMsg = xhr.responseJSON.error;
          }
          $('#paymentDetailsContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> ${errorMsg}
                        <br><small>Спробуйте оновити сторінку та повторити спробу.</small>
                    </div>
                `);
          console.error('AJAX Error:', status, error, xhr.responseText);
        }
      });
    }
  </script>
@endpush