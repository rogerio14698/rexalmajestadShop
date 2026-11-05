<x-app title="Mi cuenta | Rexalmajestad">
    <section class="account-page" aria-labelledby="account-title">
        <header>
            <h1 id="account-title">Mi cuenta</h1>
            <p>Bienvenido a tu panel de usuario.</p>
        </header>

        @if (session('status'))
            <p class="account-status" role="status">{{ session('status') }}</p>
        @endif

        <x-account-navigation />

        <section aria-labelledby="recent-orders-title">
            <h2 id="recent-orders-title">Pedidos recientes</h2>
            <ul class="account-orders-list">
                @forelse ($orders as $order)
                    <li class="account-order-card">
                        <div class="account-order-card__info">
                            <strong>{{ $order->number }}</strong>
                            <span>{{ $order->created_at?->format('d/m/Y') }} · {{ number_format($order->total_cents / 100, 2, ',', '.') }} {{ $order->currency }}</span>
                        </div>
                        <span class="account-order-card__badge">{{ $order->status->label() }}</span>
                        <a class="button button--secondary" href="{{ route('account.orders.show', $order) }}">Ver pedido</a>
                    </li>
                @empty
                    <p>Aún no tienes pedidos.</p>
                @endforelse
            </ul>
        </section>

        <section class="account-privacy-box" aria-labelledby="privacy-rights-title">
            <h2 id="privacy-rights-title">Privacidad y datos</h2>
            <p><a class="text-link" href="{{ route('account.export') }}">Descargar mis datos personales (ZIP/CSV)</a></p>

            <form method="POST" action="{{ route('account.privacy.store') }}">
                @csrf
                <h3>Solicitar ejercicio de derechos</h3>
                <label for="privacy-type">Tipo de solicitud</label>
                <select id="privacy-type" name="type">
                    <option value="access">Acceso</option>
                    <option value="rectification">Rectificación</option>
                    <option value="erasure">Supresión</option>
                </select>
                <x-form-field name="message" label="Detalles (opcional)" type="textarea" />
                <x-button type="submit">Enviar solicitud</x-button>
            </form>
        </section>
    </section>
</x-app>
