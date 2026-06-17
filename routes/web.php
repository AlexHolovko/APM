<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\HomePageController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\ClientController;
use App\Http\Controllers\Manager\PolicyController;
use App\Http\Controllers\Manager\PolicyTypeController;
use App\Http\Controllers\Manager\AnalyticsController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\AuditController;

use App\Http\Controllers\Specialist\SpecialistController;
use App\Http\Controllers\Accountant\AccountantController;

use App\Models\AuditLog;

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/

Route::get('/', [HomePageController::class, 'index']);

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

// Login page
Route::get('/login', function () {
  return view('auth.login');
})->middleware('guest')->name('login');

// Login handler
Route::post('/login', function (Request $request) {

  if (Auth::attempt($request->only('email', 'password'))) {
    $request->session()->regenerate();

    // Логирование успешного входа
    try {
      AuditLog::create([
        'user_id' => Auth::id(),
        'action' => 'login',
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'details' => json_encode([
          'email' => $request->email,
          'role' => auth()->user()->role,
          'success' => true
        ])
      ]);
    } catch (\Exception $e) {
      // Игнорируем ошибки логирования
    }

    return match (auth()->user()->role) {
      'admin' => redirect('/admin'),
      'manager' => redirect('/manager'),
      'specialist' => redirect('/specialist'),
      'accountant' => redirect('/accountant'), // Перенаправляем бухгалтера на его страницы
      default => redirect('/home'),
    };
  }

  // Логирование неудачной попытки входа
  try {
    AuditLog::create([
      'user_id' => null,
      'action' => 'login_failed',
      'ip_address' => $request->ip(),
      'user_agent' => $request->userAgent(),
      'details' => json_encode([
        'email' => $request->email,
        'success' => false,
        'reason' => 'Невірний email або пароль'
      ])
    ]);
  } catch (\Exception $e) {
    // Игнорируем ошибки логирования
  }

  return back()->withErrors(['error' => 'Невірні дані']);
})->name('login.perform');

// Logout
Route::match(['get', 'post'], '/logout', function () {

  // Логирование выхода
  if (Auth::check()) {
    try {
      AuditLog::create([
        'user_id' => Auth::id(),
        'action' => 'logout',
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'details' => json_encode([
          'email' => Auth::user()->email,
          'role' => Auth::user()->role
        ])
      ]);
    } catch (\Exception $e) {
      // Игнорируем ошибки логирования
    }
  }

  Auth::logout();
  return redirect('/login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| PROTECTED
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

  Route::get('/home', fn() => view('home'));

  /*
  |--------------------------------------------------------------------------
  | ADMIN
  |--------------------------------------------------------------------------
  */
  Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('roles/{role}/permissions', [RoleController::class, 'getPermissions'])->name('roles.permissions');
    Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->name('roles.permissions.sync');
    Route::resource('audit', AuditController::class)->only(['index', 'show']);
  });

  /*
  |--------------------------------------------------------------------------
  | MANAGER
  |--------------------------------------------------------------------------
  */
  Route::middleware(['auth', 'role:manager'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {
        
        // Dashboard менеджера
        Route::get('/', [ManagerDashboardController::class, 'index'])->name('dashboard');
        
        // CRM - Клієнти
        Route::resource('clients', ClientController::class)->except(['show']);
        
        // Поліси
        Route::resource('policies', PolicyController::class)->except(['show', 'create', 'edit']);
        
        // Типи полісів
        Route::resource('policy-types', PolicyTypeController::class)->except(['show', 'create', 'edit']);
        
        // Аналітика
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    });

  /*
  |--------------------------------------------------------------------------
  | ACCOUNTANT (доступ для менеджера)
  |--------------------------------------------------------------------------
  */
  Route::middleware(['auth', 'role:manager'])  // ← доступ для менеджера
    ->prefix('accountant')
    ->name('accountant.')
    ->group(function () {
        
        Route::get('/', [AccountantController::class, 'dashboard'])->name('dashboard');
        
        // Платежі (прострочені)
        Route::get('/payments', [AccountantController::class, 'payments'])->name('payments');
        
        // Виплати
        Route::get('/payouts', [AccountantController::class, 'payouts'])->name('payouts');
        Route::get('/payouts/{id}', [AccountantController::class, 'show'])->name('payouts.show');
        Route::put('/payouts/{id}', [AccountantController::class, 'update'])->name('payouts.update');
        
        // Заборгованості
        Route::get('/debts', [AccountantController::class, 'debts'])->name('debts');
        
        // Звіти
        Route::get('/reports', [AccountantController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [AccountantController::class, 'exportReport'])->name('reports.export');
        Route::get('/reports/export-word', [AccountantController::class, 'exportReportToWord'])->name('reports.export-word');
        Route::get('/payment/{id}', [AccountantController::class, 'getPaymentDetails'])->name('payment.details');
    });

  /*
  |--------------------------------------------------------------------------
  | SPECIALIST
  |--------------------------------------------------------------------------
  */
  Route::middleware(['auth', 'role:specialist'])
    ->prefix('specialist')
    ->name('specialist.')
    ->group(function () {

      // Dashboard
      Route::get('/', [SpecialistController::class, 'dashboard'])->name('dashboard');

      // Список випадків
      Route::get('/cases', [SpecialistController::class, 'cases'])->name('cases');

      // Створення випадку
      Route::get('/case/create', [SpecialistController::class, 'create'])->name('case.create');
      Route::post('/case', [SpecialistController::class, 'store'])->name('case.store');

      // Перегляд випадку
      Route::get('/case/{id}', [SpecialistController::class, 'show'])->name('case.show');

      // Розгляд випадку
      Route::get('/case/{id}/review', [SpecialistController::class, 'review'])->name('case.review');

      // Оновлення статусу
      Route::put('/case/{id}/status', [SpecialistController::class, 'updateStatus'])->name('case.status');

      // Редагування випадку
      Route::get('/case/{id}/edit', [SpecialistController::class, 'edit'])->name('case.edit');
      Route::put('/case/{id}', [SpecialistController::class, 'update'])->name('case.update');

      // Видалення випадку
      Route::delete('/case/{id}', [SpecialistController::class, 'destroy'])->name('case.destroy');

      // Пошук поліса (AJAX)
      Route::get('/search-policy', [SpecialistController::class, 'searchPolicy'])->name('policy.search');
      Route::get('/policy/{id}', [SpecialistController::class, 'getPolicy'])->name('policy.get');
    });

  /*
  |--------------------------------------------------------------------------
  | PROFILE
  |--------------------------------------------------------------------------
  */
  Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('index');
    Route::post('/password', [ProfileController::class, 'updatePassword'])->name('password');
  });

  // Добавлен алиас для route('profile')
  Route::get('/profile-alias', [ProfileController::class, 'index'])->name('profile');
});