<?php

use Illuminate\Foundation\Inspiring;
use App\Jobs\ReleaseExpiredStockReservations;
use App\Models\Order;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new ReleaseExpiredStockReservations)->everyMinute();

Artisan::command('invoices:issue-pending', function (InvoiceService $invoiceService) {
    if (! $invoiceService->issuerConfigured()) {
        $this->error('Configura primero los datos fiscales BUSINESS_* del emisor.');

        return self::FAILURE;
    }

    $count = 0;
    Order::query()
        ->where('status', 'paid')
        ->doesntHave('invoice')
        ->orderBy('id')
        ->chunkById(100, function ($orders) use ($invoiceService, &$count) {
            foreach ($orders as $order) {
                $invoiceService->issueForOrder($order);
                $count++;
            }
        });

    $this->info("Facturas emitidas: {$count}");

    return self::SUCCESS;
})->purpose('Emitir facturas pendientes una vez configurados los datos fiscales');

Artisan::command('app:promote-admin {email}', function (string $email) {
    $user = User::query()->where('email', $email)->first();

    if (! $user) {
        $this->error('No existe una cuenta con ese correo.');

        return self::FAILURE;
    }

    $user->forceFill([
        'role' => 'admin',
        'email_verified_at' => $user->email_verified_at ?? now(),
    ])->save();

    $this->info("Acceso de administración habilitado para {$user->email}.");

    return self::SUCCESS;
})->purpose('Habilitar acceso Filament a una cuenta existente verificada');
