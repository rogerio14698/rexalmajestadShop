<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminMetricsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $paidOrders = Order::query()->whereIn('status', ['paid', 'preparing', 'shipped', 'delivered']);

        return [
            Stat::make('Pedidos pagados', (clone $paidOrders)->count()),
            Stat::make(
                'Ingresos confirmados',
                number_format((clone $paidOrders)->sum('total_cents') / 100, 2, ',', '.').' '.config('commerce.currency', 'EUR'),
            ),
            Stat::make('Pendientes de pago', Order::query()->where('status', 'pending')->count()),
        ];
    }
}
