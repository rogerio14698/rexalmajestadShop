@php
    $artworkImage = $artwork->getFirstMediaUrl('artwork', 'webp') ?: $artwork->getFirstMediaUrl('artwork');
@endphp

<x-app :title="'Consultar envío: '.$artwork->title.' | Rexalmajestad'">
    <x-breadcrumbs :items="[
        $artwork->categoryLabel() ?: 'Catálogo' => $artwork->category ? route('artworks.category', ['category' => $artwork->category->slug]) : route('artworks.index'),
        $artwork->title => route('artworks.show', ['category' => $artwork->category?->slug, 'slug' => $artwork->slug]),
        'Consultar envío' => '',
    ]" />

    <section class="content-page shipping-inquiry-page" aria-labelledby="shipping-inquiry-title">
        @if ($artworkImage)
            <div class="shipping-inquiry-backdrop" aria-hidden="true">
                <img src="{{ $artworkImage }}" alt="">
                <div class="shipping-inquiry-backdrop__overlay"></div>
            </div>
        @endif

        <div class="contact-card shipping-inquiry-card" data-reveal>
            <header class="contact-card__header">
                <p class="shipping-inquiry-card__eyebrow">{{ $artwork->categoryLabel() }} · {{ $artwork->year }}</p>
                <h1 id="shipping-inquiry-title">Consultar envío</h1>
                <p class="contact-card__lede">
                    Solicita una cotización personalizada de embalaje y transporte seguro para la obra <strong>«{{ $artwork->title }}»</strong>.
                </p>
            </header>

            @if (session('status'))
                <div class="contact-card__status" role="status">
                    <p>{{ session('status') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('shipping-inquiry.store', ['category' => $artwork->category?->slug, 'slug' => $artwork->slug]) }}" class="contact-form">
                @csrf
                <div class="contact-form__row">
                    <x-form-field name="name" label="Nombre completo" required autocomplete="name" />
                    <x-form-field name="email" label="Correo electrónico" type="email" required autocomplete="email" />
                </div>
                <div class="contact-form__row">
                    <x-form-field name="country_code" label="País (código ISO 2 letras)" required placeholder="ES" />
                    <x-form-field name="postal_code" label="Código postal" placeholder="28001" />
                </div>
                <x-form-field name="message" label="Detalles o instrucciones de entrega (opcional)" type="textarea" rows="4" placeholder="Indica ciudad de destino o cualquier requerimiento especial..." />
                <x-form-field name="website" label="Sitio web" tabindex="-1" autocomplete="off" class="checkout-form__honeypot" />
                <div class="contact-form__actions">
                    <x-button type="submit">Enviar solicitud de cotización</x-button>
                </div>
            </form>
        </div>
    </section>
</x-app>
