<x-app title="Mis pedidos | Rexalmajestad">
    <section class="account-page" aria-labelledby="orders-title">
        <header>
            <h1 id="orders-title">Mis pedidos</h1>
        </header>

        <x-account-navigation :breadcrumbs="['Mis pedidos' => '']" />

        <ul class="account-orders-list">
            @forelse ($orders as $order)
                <li class="account-order-card">
                    <div class="account-order-card__info">
                        <strong>{{ $order->number }}</strong>
                        <span>{{ $order->created_at?->format('d/m/Y') }} · {{ number_format($order->total_cents / 100, 2, ',', '.') }} {{ $order->currency }}</span>
                    </div>
                    <span class="account-order-card__badge">{{ $order->status->label() }}</span>
                    <a class="button button--secondary" href="{{ route('account.orders.show', $order) }}">Ver detalle</a>
                </li>
            @empty
                <p>No tienes pedidos registrados.</p>
            @endforelse
        </ul>

        {{ $orders->links() }}
    </section>
</x-app>
