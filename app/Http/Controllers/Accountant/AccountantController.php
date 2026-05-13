<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\InsuranceCase;
use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountantController extends Controller
{
  public function dashboard()
  {
    // Надходження (всі виконані платежі премій)
    $totalPayments = Payment::where('payment_type', 'premium')
      ->where('status', 'completed')
      ->sum('amount');

    // Виплати (всі виконані виплати)
    $totalPayouts = Payment::where('payment_type', 'payout')
      ->where('status', 'completed')
      ->sum('amount');

    // Очікує виплати (схвалені випадки, які ще не виплачені)
    $pendingPayouts = InsuranceCase::where('status', 'approved')
      ->where('payment_status', 'pending')
      ->sum('approved_amount');

    // Кількість ПРОСТРОЧЕНИХ ПЛАТЕЖІВ (42)
    $overduePaymentsCount = Payment::where('payment_type', 'premium')
      ->where('status', 'pending')
      ->where('date', '<', now())
      ->count();

    // Останні операції
    $recentPayments = Payment::with(['policy.client'])
      ->orderBy('created_at', 'desc')
      ->limit(10)
      ->get();

    // Місячна статистика для графіка
    $monthlyStats = Payment::select(
      DB::raw('MONTH(date) as month'),
      DB::raw('SUM(CASE WHEN payment_type = "premium" AND status = "completed" THEN amount ELSE 0 END) as total_premiums'),
      DB::raw('SUM(CASE WHEN payment_type = "payout" AND status = "completed" THEN amount ELSE 0 END) as total_payouts')
    )
      ->whereYear('date', date('Y'))
      ->groupBy('month')
      ->orderBy('month')
      ->get();

    return view('accountant.dashboard', compact(
      'totalPayments',
      'totalPayouts',
      'pendingPayouts',
      'overduePaymentsCount', 
      'recentPayments',
      'monthlyStats'
    ));
  }

  public function getPaymentDetails($id)
  {
    try {
      $payment = Payment::with(['policy.client'])->findOrFail($id);

      $data = [
        'id' => $payment->id,
        'date' => $payment->date ? \Carbon\Carbon::parse($payment->date)->format('d.m.Y H:i') : '-',
        'type' => $payment->payment_type == 'premium' ? 'Надходження (страхова премія)' : 'Виплата (страхове відшкодування)',
        'amount' => number_format($payment->amount, 2),
        'status' => $payment->status == 'completed' ? 'Виконано' : 'Очікує',
        'status_class' => $payment->status == 'completed' ? 'success' : 'warning',
        'transaction_id' => $payment->transaction_id ?? 'Не вказано',
        'policy_number' => $payment->policy->policy_number ?? 'N/A',
        'client_name' => $payment->policy && $payment->policy->client
          ? trim($payment->policy->client->last_name . ' ' . $payment->policy->client->first_name . ' ' . ($payment->policy->client->middle_name ?? ''))
          : 'Немає даних',
        'client_phone' => $payment->policy && $payment->policy->client && $payment->policy->client->phone
          ? $payment->policy->client->phone
          : 'Не вказано',
        'client_email' => $payment->policy && $payment->policy->client && $payment->policy->client->email
          ? $payment->policy->client->email
          : 'Не вказано',
        'description' => $payment->description ?? 'Немає опису',
        'created_at' => $payment->created_at ? \Carbon\Carbon::parse($payment->created_at)->format('d.m.Y H:i') : '-',
      ];

      return response()->json($data);

    } catch (\Exception $e) {
      return response()->json(['error' => 'Платіж не знайдено: ' . $e->getMessage()], 404);
    }
  }

  public function payouts(Request $request)
  {
    $query = InsuranceCase::with(['policy.client'])
      ->where('status', 'approved')
      ->whereNotNull('approved_amount');

    if ($request->status && $request->status != 'all') {
      $query->where('payment_status', $request->status);
    }

    if ($request->date_from) {
      $query->whereDate('decision_date', '>=', $request->date_from);
    }

    if ($request->date_to) {
      $query->whereDate('decision_date', '<=', $request->date_to);
    }

    if ($request->policy_number) {
      $query->whereHas('policy', function ($q) use ($request) {
        $q->where('policy_number', 'like', '%' . $request->policy_number . '%');
      });
    }

    $payouts = $query->orderBy('created_at', 'desc')->paginate(15);
    $payouts->appends($request->except('page'));

    $stats = [
      'total_pending' => InsuranceCase::where('status', 'approved')
        ->where('payment_status', 'pending')
        ->sum('approved_amount'),
      'total_completed' => InsuranceCase::where('status', 'approved')
        ->where('payment_status', 'paid')
        ->sum('approved_amount'),
      'total_rejected' => InsuranceCase::where('status', 'approved')
        ->where('payment_status', 'rejected')
        ->sum('approved_amount'),
    ];

    return view('accountant.payouts', compact('payouts', 'stats'));
  }

  public function show($id)
  {
    $payout = InsuranceCase::with(['policy.client', 'policy.policyType'])
      ->where('status', 'approved')
      ->findOrFail($id);

    return view('accountant.show', compact('payout'));
  }

  public function update(Request $request, $id)
  {
    $payout = InsuranceCase::findOrFail($id);

    $request->validate([
      'status' => 'required|in:pending,paid,rejected',
    ]);

    $payout->payment_status = $request->status;

    if ($request->status == 'paid' && $request->transaction_id) {
      Payment::create([
        'policy_id' => $payout->policy_id,
        'insurance_case_id' => $payout->id,
        'amount' => $payout->approved_amount,
        'payment_type' => 'payout',
        'date' => $request->payment_date ?? now(),
        'status' => 'completed',
        'transaction_id' => $request->transaction_id,
      ]);
    }

    if ($request->notes) {
      $payout->decision_notes = $request->notes;
    }

    $payout->save();

    return redirect()->route('accountant.payouts.show', $payout->id)
      ->with('success', 'Статус виплати оновлено!');
  }

  public function payments(Request $request)
  {
    $query = Payment::with(['policy.client'])
      ->where('payment_type', 'premium')
      ->where('status', 'pending');

    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->whereHas('policy', function ($q) use ($search) {
          $q->where('policy_number', 'like', "%{$search}%");
        })->orWhereHas('policy.client', function ($q) use ($search) {
          $q->where('last_name', 'like', "%{$search}%")
            ->orWhere('first_name', 'like', "%{$search}%")
            ->orWhere('middle_name', 'like', "%{$search}%");
        });
      });
    }

    $payments = $query->orderBy('date', 'desc')->paginate(15);
    $payments->appends($request->except('page'));

    $now = now();
    $stats = [
      'total_pending' => Payment::where('payment_type', 'premium')
        ->where('status', 'pending')
        ->sum('amount'),
      'total_overdue' => Payment::where('payment_type', 'premium')
        ->where('status', 'pending')
        ->where('date', '<', $now)
        ->sum('amount'),
    ];

    return view('accountant.payments', compact('payments', 'stats'));
  }

  public function reports(Request $request)
  {
    $reportType = $request->get('report_type', 'monthly');
    $year = $request->get('year', date('Y'));
    $month = $request->get('month', date('m'));

    $monthlyTrend = [];
    for ($m = 1; $m <= 12; $m++) {
      $monthlyTrend[$m] = [
        'premiums' => Payment::where('payment_type', 'premium')
          ->where('status', 'completed')
          ->whereYear('date', $year)
          ->whereMonth('date', $m)
          ->sum('amount'),
        'payouts' => Payment::where('payment_type', 'payout')
          ->where('status', 'completed')
          ->whereYear('date', $year)
          ->whereMonth('date', $m)
          ->sum('amount'),
        'profit' => 0
      ];
      $monthlyTrend[$m]['profit'] = $monthlyTrend[$m]['premiums'] - $monthlyTrend[$m]['payouts'];
    }

    $query = Payment::select(
      DB::raw('DATE(date) as date'),
      DB::raw('SUM(CASE WHEN payment_type = "premium" AND status = "completed" THEN amount ELSE 0 END) as premiums'),
      DB::raw('SUM(CASE WHEN payment_type = "payout" AND status = "completed" THEN amount ELSE 0 END) as payouts'),
      DB::raw('COUNT(CASE WHEN payment_type = "premium" THEN 1 END) as premiums_count'),
      DB::raw('COUNT(CASE WHEN payment_type = "payout" THEN 1 END) as payouts_count')
    )
      ->whereYear('date', $year);

    if ($reportType == 'monthly') {
      $query->whereMonth('date', $month);
    }

    $paymentsReport = $query->groupBy('date')
      ->orderBy('date', 'desc')
      ->get();

    $topClients = Policy::select('clients.last_name', 'clients.first_name', DB::raw('SUM(payments.amount) as total_paid'))
      ->join('clients', 'policies.client_id', '=', 'clients.id')
      ->join('payments', 'policies.id', '=', 'payments.policy_id')
      ->where('payments.payment_type', 'premium')
      ->where('payments.status', 'completed')
      ->groupBy('clients.id', 'clients.last_name', 'clients.first_name')
      ->orderBy('total_paid', 'desc')
      ->limit(5)
      ->get();

    $policyStats = Policy::select(
      'policy_types.name as policy_type',
      DB::raw('COUNT(DISTINCT policies.id) as total_policies'),
      DB::raw('SUM(payments.amount) as total_premium')
    )
      ->join('policy_types', 'policies.policy_type_id', '=', 'policy_types.id')
      ->join('payments', 'policies.id', '=', 'payments.policy_id')
      ->where('payments.payment_type', 'premium')
      ->where('payments.status', 'completed')
      ->groupBy('policy_types.name')
      ->get();

    return view('accountant.reports', compact(
      'paymentsReport',
      'topClients',
      'policyStats',
      'monthlyTrend',
      'reportType',
      'year',
      'month'
    ));
  }

  public function exportReport(Request $request)
  {
    $year = $request->get('year', date('Y'));
    $month = $request->get('month', date('m'));
    $reportType = $request->get('report_type', 'monthly');

    $payments = Payment::with(['policy.client'])
      ->whereYear('date', $year);

    if ($reportType == 'monthly') {
      $payments->whereMonth('date', $month);
    }

    $payments = $payments->get();

    $totalPremiums = $payments->where('payment_type', 'premium')->sum('amount');
    $totalPayouts = $payments->where('payment_type', 'payout')->sum('amount');
    $totalProfit = $totalPremiums - $totalPayouts;

    $filename = "financial_report_{$year}_" . ($reportType == 'monthly' ? $month : 'full') . ".csv";

    $headers = [
      'Content-Type' => 'text/csv',
      'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    $callback = function () use ($payments, $totalPremiums, $totalPayouts, $totalProfit) {
      $file = fopen('php://output', 'w');
      fputcsv($file, ['ФІНАНСОВИЙ ЗВІТ СТРАХОВОЇ КОМПАНІЇ']);
      fputcsv($file, ['Дата формування:', now()->format('d.m.Y H:i:s')]);
      fputcsv($file, []);
      fputcsv($file, ['ПІДСУМКИ']);
      fputcsv($file, ['Загальна сума надходжень:', number_format($totalPremiums, 2) . ' грн']);
      fputcsv($file, ['Загальна сума виплат:', number_format($totalPayouts, 2) . ' грн']);
      fputcsv($file, ['Чистий прибуток:', number_format($totalProfit, 2) . ' грн']);
      fputcsv($file, []);
      fputcsv($file, ['ДЕТАЛЬНИЙ ПЕРЕЛІК ОПЕРАЦІЙ']);
      fputcsv($file, ['ID', 'Дата', 'Тип', 'Сума', 'Статус', 'Номер полісу', 'Клієнт']);

      foreach ($payments as $payment) {
        fputcsv($file, [
          $payment->id,
          $payment->date ? \Carbon\Carbon::parse($payment->date)->format('d.m.Y') : '-',
          $payment->payment_type == 'premium' ? 'Надходження' : 'Виплата',
          number_format($payment->amount, 2) . ' грн',
          $payment->status == 'completed' ? 'Виконано' : 'Очікує',
          $payment->policy->policy_number ?? 'N/A',
          ($payment->policy->client->last_name ?? '') . ' ' . ($payment->policy->client->first_name ?? '')
        ]);
      }

      fclose($file);
    };

    return response()->stream($callback, 200, $headers);
  }

  public function exportReportToWord(Request $request)
  {
    $year = $request->get('year', date('Y'));
    $month = $request->get('month', date('m'));
    $reportType = $request->get('report_type', 'monthly');

    $payments = Payment::with(['policy.client'])
      ->whereYear('date', $year);

    if ($reportType == 'monthly') {
      $payments->whereMonth('date', $month);
    }

    $payments = $payments->get();

    $totalPremiums = $payments->where('payment_type', 'premium')->sum('amount');
    $totalPayouts = $payments->where('payment_type', 'payout')->sum('amount');
    $totalProfit = $totalPremiums - $totalPayouts;
    $premiumsCount = $payments->where('payment_type', 'premium')->count();
    $payoutsCount = $payments->where('payment_type', 'payout')->count();

    $periodText = ($reportType == 'monthly')
      ? date('F Y', mktime(0, 0, 0, $month, 1, $year))
      : $year;

    $filename = "financial_report_{$year}_" . ($reportType == 'monthly' ? $month : 'full') . ".doc";

    $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Фінансовий звіт</title>
            <style>
                @page { size: A4; margin: 2.5cm; }
                body { font-family: "Calibri", "Times New Roman", Arial, sans-serif; margin: 0; padding: 0; font-size: 11pt; line-height: 1.4; color: #333; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #2c3e50; padding-bottom: 15px; }
                .header h1 { font-size: 22pt; color: #1a5276; margin: 0 0 5px 0; text-transform: uppercase; }
                .header h3 { font-size: 14pt; color: #2c3e50; margin: 5px 0; font-weight: normal; }
                .company-info { text-align: center; margin-bottom: 20px; font-size: 10pt; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
                .section-title { font-size: 14pt; font-weight: bold; color: #1a5276; background-color: #e8f0fe; padding: 8px 12px; margin: 20px 0 15px 0; border-left: 5px solid #1a5276; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th { background-color: #2c3e50; color: white; padding: 10px 8px; border: 1px solid #1a5276; }
                td { padding: 8px; border: 1px solid #ddd; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .profit { color: #27ae60; font-weight: bold; }
                .loss { color: #e74c3c; font-weight: bold; }
                .footer { margin-top: 40px; text-align: center; font-size: 9pt; border-top: 1px solid #ccc; padding-top: 15px; }
                .signature { margin-top: 50px; display: flex; justify-content: space-between; }
                .signature div { width: 250px; text-align: center; }
                .badge-success { background-color: #d4edda; color: #155724; }
                .badge-warning { background-color: #fff3cd; color: #856404; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>ФІНАНСОВИЙ ЗВІТ</h1>
                <h3>СТРАХОВОЇ КОМПАНІЇ</h3>
            </div>
            
            <div class="company-info">
                <p><strong>Період формування звіту:</strong> ' . $periodText . '</p>
                <p><strong>Дата формування:</strong> ' . now()->format('d.m.Y H:i:s') . '</p>
                <p><strong>Тип звіту:</strong> ' . ($reportType == 'monthly' ? 'МІСЯЧНИЙ' : 'РІЧНИЙ') . '</p>
            </div>

            <div class="section-title">1. ВИКОНАВЧИЙ ОГЛЯД</div>
            <table>
                <tr><td width="60%"><strong>Загальна сума надходжень</strong></td><td class="text-right profit"><strong>' . number_format($totalPremiums, 2) . ' грн</strong></td></tr>
                <tr><td><strong>Загальна сума виплат</strong></td><td class="text-right">' . number_format($totalPayouts, 2) . ' грн</td></tr>
                <tr style="background-color: #e8f5e9;"><td><strong>ЧИСТИЙ ПРИБУТОК</strong></td><td class="text-right ' . ($totalProfit >= 0 ? 'profit' : 'loss') . '"><strong>' . number_format($totalProfit, 2) . ' грн</strong></td></tr>
            </table>

            <div class="section-title">2. ДЕТАЛЬНИЙ ПЕРЕЛІК ОПЕРАЦІЙ</div>
            <table>
                <thead><tr><th>№</th><th>Дата</th><th>Тип</th><th class="text-right">Сума (грн)</th><th>Статус</th><th>Поліс</th><th>Клієнт</th></tr></thead>
                <tbody>';

    $counter = 1;
    foreach ($payments as $payment) {
      $statusBadge = $payment->status == 'completed' ? 'Виконано' : 'Очікує';
      $typeText = $payment->payment_type == 'premium' ? 'Надходження' : 'Виплата';

      $html .= '<tr>
                        <td class="text-center">' . $counter++ . '</td>
                        <td class="text-center">' . \Carbon\Carbon::parse($payment->date)->format('d.m.Y') . '</td>
                        <td class="text-center">' . $typeText . '</td>
                        <td class="text-right">' . number_format($payment->amount, 2) . '</td>
                        <td class="text-center">' . $statusBadge . '</td>
                        <td>' . ($payment->policy->policy_number ?? 'N/A') . '</td>
                        <td>' . ($payment->policy->client->last_name ?? '') . ' ' . ($payment->policy->client->first_name ?? '') . '</td>
                    </tr>';
    }

    $html .= '
                </tbody>
            </table>

            <div class="signature">
                <div>_________________________<br><strong>Головний бухгалтер</strong></div>
                <div>_________________________<br><strong>Керівник</strong></div>
            </div>
            <div class="footer"><p>Звіт згенеровано автоматично ' . now()->format('d.m.Y H:i:s') . '</p></div>
        </body>
        </html>';

    $headers = [
      'Content-Type' => 'application/msword',
      'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    return response($html, 200, $headers);
  }

  public function debts()
  {
    $debts = Payment::with(['policy.client'])
      ->where('payment_type', 'premium')
      ->where('status', 'pending')
      ->where('date', '<', now())
      ->orderBy('date', 'asc')
      ->paginate(15);

    $debts->appends(request()->except('page'));

    $totalDebt = Payment::where('payment_type', 'premium')
      ->where('status', 'pending')
      ->where('date', '<', now())
      ->sum('amount');

    return view('accountant.debts', compact('debts', 'totalDebt'));
  }
}