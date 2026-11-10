<x-app :title="'Consultar envío: '.$artwork->title.' | Rexalmajestad'">
    <section class="content-page" aria-labelledby="shipping-inquiry-title">
        <h1 id="shipping-inquiry-title">Consultar el envío</h1>
        <p>Antes de comprar «{{ $artwork->title }}», pide una cotización de envío.</p>
        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif
        <form method="POST" action="{{ route('shipping-inquiry.store', ['category' => $artwork->category?->slug, 'slug' => $artwork->slug]) }}" class="checkout-form">
            @csrf
            <x-form-field name="name" label="Nombre" required autocomplete="name" />
            <x-form-field name="email" label="Correo electrónico" type="email" required autocomplete="email" />
            <x-form-field name="country_code" label="País (código ISO 2 letras)" required />
            <x-form-field name="postal_code" label="Código postal" />
            <x-form-field name="message" label="Mensaje" type="textarea" />
            <x-form-field name="website" label="Sitio web" tabindex="-1" autocomplete="off" class="checkout-form__honeypot" />
            <x-button type="submit">Enviar consulta</x-button>
        </form>
    </section>
</x-app>
