<?php

namespace App\Providers;

use App\Contracts\InvoiceRegistrar;
use App\Listeners\MergeGuestCart;
use App\Services\NullInvoiceRegistrar;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(InvoiceRegistrar::class, NullInvoiceRegistrar::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, MergeGuestCart::class);

        // Permite resolver vistas de la raíz como componentes: <x-app> usa resources/views/app.blade.php.
        Blade::anonymousComponentPath(resource_path('views'));
    }
}
