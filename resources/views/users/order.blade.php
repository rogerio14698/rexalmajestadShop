<x-app :title="'Pedido '.$order->number.' | Rexalmajestad'">
    <section class="account-page" aria-labelledby="order-title">
        <header>
            <h1 id="order-title">Pedido {{ $order->number }}</h1>
        </header>

        <x-account-navigation :breadcrumbs="['Mis pedidos' => route('account.orders'), $order->number => '']" />

        <div class="account-order-card">
            <p><strong>Estado:</strong> <span class="account-order-card__badge">{{ $order->status->label() }}</span></p>
        </div>

        <ul class="account-orders-list">
            @foreach ($order->items as $item)
                <li class="account-order-card">
                    <div class="account-order-card__info">
                        <strong>{{ $item->title }}</strong>
                        <span>Cantidad: {{ $item->quantity }}</span>
                    </div>
                    <span>{{ number_format($item->total_cents / 100, 2, ',', '.') }} {{ $order->currency }}</span>
                </li>
            @endforeach
        </ul>

        <div class="account-order-card">
            <strong>Total del pedido:</strong>
            <strong>{{ number_format($order->total_cents / 100, 2, ',', '.') }} {{ $order->currency }}</strong>
        </div>

        @if ($order->invoice)
            <p><a class="button button--secondary" href="{{ route('invoices.download', $order->invoice) }}">Descargar factura {{ $order->invoice->number }}</a></p>
        @endif
    </section>
</x-app>
