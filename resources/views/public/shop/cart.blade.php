<x-app title="Carrito | Rexalmajestad">
    <x-breadcrumbs :items="['Carrito' => '']" />

    <section class="content-page" aria-labelledby="cart-title">
        <h1 id="cart-title">Carrito</h1>
        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif
        @if ($cart->items->isEmpty())
            <p>Tu carrito está vacío.</p>
            <a class="text-link" href="{{ route('shop.index') }}">Volver a la tienda</a>
        @else
            <div class="cart-list">
                @foreach ($cart->items as $item)
                    <article class="cart-item">
                        <div>
                            <h2>{{ $item->artwork->title }}</h2>
                            <p>{{ $item->quantity }} × {{ number_format($item->artwork->price_cents / 100, 2, ',', '.') }} {{ config('commerce.currency', 'EUR') }}</p>
                        </div>
                        <div>
                            <form method="POST" action="{{ route('cart.update', ['item' => $item]) }}">
                                @csrf
                                @method('PATCH')
                                <label for="quantity-{{ $item->id }}">Cantidad</label>
                                <input id="quantity-{{ $item->id }}" name="quantity" type="number" min="1" max="{{ min(99, $item->artwork->stock) }}" value="{{ $item->quantity }}">
                                <button class="button button--quiet" type="submit">Actualizar</button>
                            </form>
                            <form method="POST" action="{{ route('cart.remove', ['item' => $item]) }}">
                                @csrf
                                @method('DELETE')
                                <button class="text-link" type="submit">Quitar</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
            <a class="button" href="{{ route('checkout.show') }}">Continuar al pago</a>
        @endif
    </section>
</x-app>
