@extends('adminlte::page')

@section('title', 'Фінансові звіти')

@section('content_header')
<h1><i class="fas fa-chart-pie"></i> Фінансові звіти</h1>
@stop

@section('content')
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Звіти та аналітика</h3>
      <div class="card-tools">
        <form method="GET" class="form-inline">
          <select name="report_type" class="form-control form-control-sm mr-2">
            <option value="monthly" {{ ($reportType ?? 'monthly') == 'monthly' ? 'selected' : '' }}>Місячний звіт</option>
            <option value="yearly" {{ ($reportType ?? 'monthly') == 'yearly' ? 'selected' : '' }}>Річний звіт</option>
          </select>
          <select name="year" class="form-control form-control-sm mr-2">
            @for($y = 2020; $y <= date('Y'); $y++)
              <option value="{{ $y }}" {{ ($year ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
          </select>
          @if(($reportType ?? 'monthly') == 'monthly')
            <select name="month" class="form-control form-control-sm mr-2">
              @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ ($month ?? date('m')) == $m ? 'selected' : '' }}>
                  {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                </option>
              @endfor
            </select>
          @endif
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-chart-line"></i> Показати
          </button>
          <div class="btn-group">
            <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
              <i class="fas fa-download"></i> Експорт
            </button>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="{{ route('accountant.reports.export-word', request()->all()) }}">
                <i class="fas fa-file-word"></i> Word документ
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="card-body">
      <!-- Графік динаміки -->
      <div class="row">
        <div class="col-md-12">
          <canvas id="trendChart" style="height: 300px; width: 100%;"></canvas>
        </div>
      </div>

      <hr>

      <!-- Детальний звіт -->
      <div class="row">
        <div class="col-md-12">
          <h5>Детальний звіт за
            {{ ($reportType ?? 'monthly') == 'monthly' ? date('F Y', mktime(0, 0, 0, $month ?? date('m'), 1)) : ($year ?? date('Y')) }}
            рік
          </h5>
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Дата</th>
                  <th>Надходження (грн)</th>
                  <th>Виплати (грн)</th>
                  <th>Прибуток (грн)</th>
                  <th>К-сть платежів</th>
                  <th>К-сть виплат</th>
                </tr>
              </thead>
              <tbody>
                @php
                  $totalPremiums = 0;
                  $totalPayouts = 0;
                @endphp
                @forelse(($paymentsReport ?? []) as $report)
                  @php
                    $totalPremiums += $report->premiums ?? 0;
                    $totalPayouts += $report->payouts ?? 0;
                  @endphp
                  <tr>
                    <td>{{ \Carbon\Carbon::parse($report->date)->format('d.m.Y') }}</td>
                    <td><span class="text-success">{{ number_format($report->premiums ?? 0, 2) }}</span></td>
                    <td><span class="text-danger">{{ number_format($report->payouts ?? 0, 2) }}</span></td>
                    <td>
                      <strong
                        class="text-{{ ($report->premiums ?? 0) - ($report->payouts ?? 0) >= 0 ? 'success' : 'danger' }}">
                        {{ number_format(($report->premiums ?? 0) - ($report->payouts ?? 0), 2) }}
                      </strong>
                    </td>
                    <td>{{ $report->premiums_count ?? 0 }}</td>
                    <td>{{ $report->payouts_count ?? 0 }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center">Немає даних за обраний період</td>
                  </tr>
                @endforelse
                @if(isset($paymentsReport) && count($paymentsReport) > 0)
                  <tr>
                    <th class="bg-light">ВСЬОГО:</th>
                    <th class="bg-light text-success">{{ number_format($totalPremiums, 2) }}</th>
                    <th class="bg-light text-danger">{{ number_format($totalPayouts, 2) }}</th>
                    <th class="bg-light">{{ number_format($totalPremiums - $totalPayouts, 2) }}</th>
                    <th class="bg-light" colspan="2"></th>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <hr>

      <!-- Топ клієнтів -->
      <div class="row">
        <div class="col-md-6">
          <h5>Топ клієнтів за платежами</h5>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Клієнт</th>
                  <th>Сума платежів</th>
                </tr>
              </thead>
              <tbody>
                @forelse(($topClients ?? []) as $index => $client)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $client->last_name ?? '' }} {{ $client->first_name ?? '' }}</td>
                    <td><strong>{{ number_format($client->total_paid ?? 0, 2) }} грн</strong></td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="3" class="text-center">Немає даних</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <div class="col-md-6">
          <h5>Статистика по типах полісів</h5>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Тип полісу</th>
                  <th>Кількість</th>
                  <th>Загальна сума</th>
                </tr>
              </thead>
              <tbody>
                @forelse(($policyStats ?? []) as $stat)
                  <tr>
                    <td>{{ $stat->policy_type ?? 'N/A' }}</td>
                    <td>{{ $stat->total_policies ?? 0 }}</td>
                    <td>{{ number_format($stat->total_premium ?? 0, 2) }} грн</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="3" class="text-center">Немає даних</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('js')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const monthlyTrend = @json($monthlyTrend ?? []);
    const months = ['Січ', 'Лют', 'Бер', 'Кві', 'Тра', 'Чер', 'Лип', 'Сер', 'Вер', 'Жов', 'Лис', 'Гру'];

    const premiumsData = [];
    const payoutsData = [];
    const profitData = [];

    for (let i = 1; i <= 12; i++) {
      premiumsData.push(monthlyTrend[i]?.premiums || 0);
      payoutsData.push(monthlyTrend[i]?.payouts || 0);
      profitData.push(monthlyTrend[i]?.profit || 0);
    }

    const ctx = document.getElementById('trendChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: months,
        datasets: [
          {
            label: 'Надходження (грн)',
            data: premiumsData,
            backgroundColor: 'rgba(40, 167, 69, 0.7)',
            borderColor: '#28a745',
            borderWidth: 1
          },
          {
            label: 'Виплати (грн)',
            data: payoutsData,
            backgroundColor: 'rgba(220, 53, 69, 0.7)',
            borderColor: '#dc3545',
            borderWidth: 1
          },
          {
            label: 'Прибуток (грн)',
            data: profitData,
            type: 'line',
            backgroundColor: 'rgba(23, 162, 184, 0.1)',
            borderColor: '#17a2b8',
            borderWidth: 2,
            fill: false,
            tension: 0.4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'top',
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Сума (грн)'
            }
          }
        }
      }
    });

    function exportReport() {
      const params = new URLSearchParams(window.location.search);
      window.location.href = '{{ route("accountant.reports.export") }}?' + params.toString() + '&format=csv';
    }
  </script>
@endpush