<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
class AuthServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    $this->registerPolicies();

    Gate::define('admin', fn($user) => $user->hasRole('admin'));
    Gate::define('manager', fn($user) => $user->hasRole('manager'));
    Gate::define('specialist', fn($user) => $user->hasRole('specialist'));
    Gate::define('accountant', fn($user) => $user->hasRole('accountant'));
    Gate::define('auth', fn($user) => $user !== null);
    Gate::define('isGuest', function ($user = null) {
      return $user === null;
    });
  }
}
