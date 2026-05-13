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
      'accountant' => redirect('/accountant'),
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
  Route::middleware('role:manager')
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {

      // Dashboard
      Route::get('/', [ManagerDashboardController::class, 'index'])->name('dashboard');

      // CLIENTS
      Route::resource('clients', ClientController::class)
        ->except(['show']);

      // POLICIES
      Route::resource('policies', PolicyController::class)
        ->except(['show', 'create', 'edit']);

      // POLICY TYPES
      Route::resource('policy-types', PolicyTypeController::class)
        ->except(['show', 'create', 'edit']);

      // ANALYTICS
      Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
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
      Route::get('/', [App\Http\Controllers\Specialist\SpecialistController::class, 'dashboard'])->name('dashboard');

      // Список випадків
      Route::get('/cases', [App\Http\Controllers\Specialist\SpecialistController::class, 'cases'])->name('cases');

      // Створення випадку
      Route::get('/case/create', [App\Http\Controllers\Specialist\SpecialistController::class, 'create'])->name('case.create');
      Route::post('/case', [App\Http\Controllers\Specialist\SpecialistController::class, 'store'])->name('case.store');

      // Перегляд випадку
      Route::get('/case/{id}', [App\Http\Controllers\Specialist\SpecialistController::class, 'show'])->name('case.show');

      // Розгляд випадку
      Route::get('/case/{id}/review', [App\Http\Controllers\Specialist\SpecialistController::class, 'review'])->name('case.review');

      // Оновлення статусу
      Route::put('/case/{id}/status', [App\Http\Controllers\Specialist\SpecialistController::class, 'updateStatus'])->name('case.status');

      // Редагування випадку
      Route::get('/case/{id}/edit', [App\Http\Controllers\Specialist\SpecialistController::class, 'edit'])->name('case.edit');
      Route::put('/case/{id}', [App\Http\Controllers\Specialist\SpecialistController::class, 'update'])->name('case.update');

      // Видалення випадку
      Route::delete('/case/{id}', [App\Http\Controllers\Specialist\SpecialistController::class, 'destroy'])->name('case.destroy');

      // Пошук поліса (AJAX)
      Route::get('/search-policy', [App\Http\Controllers\Specialist\SpecialistController::class, 'searchPolicy'])->name('policy.search');
      Route::get('/policy/{id}', [App\Http\Controllers\Specialist\SpecialistController::class, 'getPolicy'])->name('policy.get');
    });
 /*
/*
|--------------------------------------------------------------------------
| ACCOUNTANT
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:accountant'])
    ->prefix('accountant')
    ->name('accountant.')
    ->group(function () {
        
        Route::get('/', [App\Http\Controllers\Accountant\AccountantController::class, 'dashboard'])->name('dashboard');
        
        // Платежі (прострочені)
        Route::get('/payments', [App\Http\Controllers\Accountant\AccountantController::class, 'payments'])->name('payments');
        
        // Виплати
        Route::get('/payouts', [App\Http\Controllers\Accountant\AccountantController::class, 'payouts'])->name('payouts');
        Route::get('/payouts/{id}', [App\Http\Controllers\Accountant\AccountantController::class, 'show'])->name('payouts.show');
        Route::put('/payouts/{id}', [App\Http\Controllers\Accountant\AccountantController::class, 'update'])->name('payouts.update');
        
        // Заборгованості
        Route::get('/debts', [App\Http\Controllers\Accountant\AccountantController::class, 'debts'])->name('debts');
        
        // Звіти
        Route::get('/reports', [App\Http\Controllers\Accountant\AccountantController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [App\Http\Controllers\Accountant\AccountantController::class, 'exportReport'])->name('reports.export');
        Route::get('/reports/export-word', [App\Http\Controllers\Accountant\AccountantController::class, 'exportReportToWord'])->name('reports.export-word');
        Route::get('/accountant/payment/{id}', [App\Http\Controllers\Accountant\AccountantController::class, 'getPaymentDetails'])->name('accountant.payment.details');
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