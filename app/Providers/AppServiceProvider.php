<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // @canAccess('menu.submenu.tab') ... @endCanAccess
        Blade::if('canAccess', function (string $permission) {
            $user = auth()->user();
            if (!$user) return false;
            return $user->hasPermission($permission);
        });
    }
}
