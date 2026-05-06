<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PolicyController;
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
    return match (auth()->user()->role) {
      'admin' => redirect('/admin'),
      'manager' => redirect('/manager'),
      'specialist' => redirect('/specialist'),
      'accountant' => redirect('/accountant'),
      default => redirect('/home'),
    };
  }

  return back()->withErrors(['error' => 'Невірні дані']);
})->name('login.perform');

// Logout
Route::match(['get', 'post'], '/logout', function () {
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
  Route::middleware('role:admin')->prefix('admin')->group(function () {

    Route::get('/', fn() => view('admin.dashboard'));

    Route::get('/users', fn() => view('admin.users'));
    Route::get('/roles', fn() => view('admin.roles'));
    Route::get('/audit', fn() => view('admin.audit'));
  });

  /*
  |--------------------------------------------------------------------------
  | MANAGER (АРМ)
  |--------------------------------------------------------------------------
  */
  // ==================== MANAGER ROUTES ====================
  Route::middleware(['auth', 'role:manager'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {

      // Dashboard
      Route::get('/', [\App\Http\Controllers\Manager\DashboardController::class, 'index'])
        ->name('dashboard');

      // Клієнти
      Route::get('/clients', [\App\Http\Controllers\Manager\ClientController::class, 'index'])
        ->name('clients.index');

      Route::get('/clients/create', [\App\Http\Controllers\Manager\ClientController::class, 'create'])
        ->name('clients.create');

      Route::post('/clients', [\App\Http\Controllers\Manager\ClientController::class, 'store'])
        ->name('clients.store');

      Route::get('/clients/{client}/edit', [\App\Http\Controllers\Manager\ClientController::class, 'edit'])
        ->name('clients.edit');

      Route::put('/clients/{client}', [\App\Http\Controllers\Manager\ClientController::class, 'update'])
        ->name('clients.update');

      Route::delete('/clients/{client}', [\App\Http\Controllers\Manager\ClientController::class, 'destroy'])
        ->name('clients.destroy');

      // ==================== ПОЛІСИ ====================
      Route::prefix('manager')->name('manager.')->middleware(['auth', 'role:manager'])->group(function () {

        Route::get('/policies', [PolicyController::class, 'index'])
          ->name('policies.index');

        Route::post('/policies', [PolicyController::class, 'store'])
          ->name('policies.store');

        Route::put('/policies/{policy}', [PolicyController::class, 'update'])
          ->name('policies.update');

        Route::delete('/policies/{policy}', [PolicyController::class, 'destroy'])
          ->name('policies.destroy');

      });
    });
  /*
  |--------------------------------------------------------------------------
  | SPECIALIST (страхові випадки)
  |--------------------------------------------------------------------------
  */
  Route::middleware('role:specialist')->prefix('specialist')->group(function () {

    Route::get('/', fn() => view('specialist.dashboard'));

    Route::get('/cases', fn() => view('specialist.cases'));
    Route::get('/reviews', fn() => view('specialist.reviews'));
    Route::get('/reports', fn() => view('specialist.reports'));
    Route::get('/claims-payments', fn() => view('specialist.claims_payments'));
  });

  /*
  |--------------------------------------------------------------------------
  | ACCOUNTANT
  |--------------------------------------------------------------------------
  */
  Route::middleware('role:accountant')->prefix('accountant')->group(function () {

    Route::get('/', fn() => view('accountant.dashboard'));

    Route::get('/payments', fn() => view('accountant.payments'));
    Route::get('/payouts', fn() => view('accountant.payouts'));
    Route::get('/debts', fn() => view('accountant.debts'));
    Route::get('/reports', fn() => view('accountant.reports'));
  });

  /*
  |--------------------------------------------------------------------------
  | PROFILE
  |--------------------------------------------------------------------------
  */
  Route::get('/profile', [ProfileController::class, 'index'])->name('profile');

  Route::post('/profile/password', [ProfileController::class, 'updatePassword'])
    ->name('profile.password');
});