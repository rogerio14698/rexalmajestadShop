<x-app :title="'Pedido '.$order->number.' | Administración'">
    <section class="content-page" aria-labelledby="admin-order-title">
        <h1 id="admin-order-title">Pedido {{ $order->number }}</h1>
        <p>{{ $order->email }} · {{ $order->status->label() }}</p>
        <h2>Envío</h2>
        <p>{{ implode(', ', array_filter($order->shipping_address_snapshot)) }}</p>
        <h2>Facturación</h2>
        <p>{{ implode(', ', array_filter($order->billing_address_snapshot)) }}</p>
        <h2>Artículos</h2>
        <ul>
            @foreach ($order->items as $item)
                <li>{{ $item->title }} × {{ $item->quantity }} — {{ number_format($item->total_cents / 100, 2, ',', '.') }} {{ $order->currency }}</li>
            @endforeach
        </ul>
        <p>Total: {{ number_format($order->total_cents / 100, 2, ',', '.') }} {{ $order->currency }}</p>
        @if ($order->tracking_number)
            <p>Seguimiento: {{ $order->tracking_number }}</p>
        @endif
    </section>
</x-app>
