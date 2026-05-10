<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\HomePageController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Manager\DashboardController;
use App\Http\Controllers\Manager\ClientController;
use App\Http\Controllers\Manager\PolicyController;
use App\Http\Controllers\Manager\PolicyTypeController;
use App\Http\Controllers\Manager\AnalyticsController;

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
  | MANAGER
  |--------------------------------------------------------------------------
  */
  Route::middleware('role:manager')
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {

      // Dashboard
      Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

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
  Route::middleware('role:specialist')->prefix('specialist')->name('specialist.')->group(function () {

    Route::get('/', fn() => view('specialist.dashboard'))->name('dashboard');

    Route::get('/cases', fn() => view('specialist.cases'))->name('cases');
    Route::get('/reviews', fn() => view('specialist.reviews'))->name('reviews');
    Route::get('/reports', fn() => view('specialist.reports'))->name('reports');
    Route::get('/claims-payments', fn() => view('specialist.claims_payments'))->name('claims-payments');
  });

  /*
  |--------------------------------------------------------------------------
  | ACCOUNTANT
  |--------------------------------------------------------------------------
  */
  Route::middleware('role:accountant')->prefix('accountant')->name('accountant.')->group(function () {

    Route::get('/', fn() => view('accountant.dashboard'))->name('dashboard');

    Route::get('/payments', fn() => view('accountant.payments'))->name('payments');
    Route::get('/payouts', fn() => view('accountant.payouts'))->name('payouts');
    Route::get('/debts', fn() => view('accountant.debts'))->name('debts');
    Route::get('/reports', fn() => view('accountant.reports'))->name('reports');
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