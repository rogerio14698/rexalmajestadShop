<x-app :title="$artwork->meta_title ?: $artwork->title.' | Rexalmajestad'" :description="$artwork->meta_description ?: $artwork->description" :canonical="route('artworks.show', ['category' => $artwork->category?->slug, 'slug' => $artwork->slug])">
    @php
        $artworkSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $artwork->title,
            'description' => $artwork->description,
            'image' => $artwork->getFirstMediaUrl('artwork', 'webp') ?: null,
            'brand' => ['@type' => 'Brand', 'name' => 'Rexalmajestad'],
            'category' => $artwork->categoryLabel(),
            'offers' => $artwork->canPurchase() ? [
                '@type' => 'Offer',
                'price' => number_format($artwork->price_cents / 100, 2, '.', ''),
                'priceCurrency' => config('commerce.currency', 'EUR'),
                'availability' => 'https://schema.org/InStock',
                'url' => route('artworks.show', ['category' => $artwork->category?->slug, 'slug' => $artwork->slug]),
            ] : null,
        ];
    @endphp
    @push('structured-data')
        <script type="application/ld+json">{!! json_encode($artworkSchema, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
    @endpush
    <section class="content-page artwork-detail" aria-labelledby="artwork-title">
        <div>
            @if ($artwork->getMedia('artwork')->isNotEmpty())
                <x-carousel :images="$artwork->getMedia('artwork')" :label="'Galería: '.$artwork->title" />
            @else
                <div class="artwork-detail__placeholder" role="img" aria-label="Imagen de muestra ficticia pendiente de sustituir por fotografía de la obra">
                    <x-motif-dna class="artwork-detail__motif" />
                    <span>Fotografía de obra pendiente</span>
                </div>
            @endif
        </div>
        <div class="artwork-detail__information">
            <p>{{ $artwork->categoryLabel() }} · {{ $artwork->year }}</p>
            <h1 id="artwork-title">{{ $artwork->title }}</h1>
            <p>{{ $artwork->technique }}</p>
            @if ($artwork->height_cm || $artwork->width_cm || $artwork->depth_cm)
                <p>
                    Medidas:
                    {{ $artwork->height_cm }} × {{ $artwork->width_cm }} cm
                    @if ($artwork->depth_cm)
                        × {{ $artwork->depth_cm }} cm
                    @endif
                </p>
            @endif
            <div class="page-prose">{{ $artwork->description }}</div>
            @if ($artwork->is_sold)
                <x-artwork-sold />
            @elseif ($artwork->is_for_sale && $artwork->stock === 0)
                <p>Temporalmente sin stock.</p>
            @elseif ($artwork->canPurchase())
                <p class="artwork-detail__price">{{ number_format($artwork->price_cents / 100, 2, ',', '.') }} {{ config('commerce.currency', 'EUR') }}</p>
                <form method="POST" action="{{ route('cart.add', ['artwork' => $artwork]) }}">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <x-button type="submit">Añadir al carrito</x-button>
                </form>
            @elseif ($artwork->ask_shipping)
                <p>Consulta las opciones de envío antes de realizar el pedido.</p>
                <a class="button" href="{{ route('shipping-inquiry.create', ['category' => $artwork->category?->slug, 'slug' => $artwork->slug]) }}">Consultar envío</a>
            @endif
            @auth
                <form method="POST" action="{{ route('favorites.toggle', ['artwork' => $artwork]) }}">
                    @csrf
                    <x-button variant="quiet" type="submit">
                        {{ auth()->user()->favorites()->whereKey($artwork->getKey())->exists() ? 'Quitar de favoritos' : 'Añadir a favoritos' }}
                    </x-button>
                </form>
            @endauth
        </div>
    </section>

    @if ($relatedArtworks->isNotEmpty())
        <section class="content-page" aria-labelledby="related-title">
            <h2 id="related-title">Otras obras</h2>
            <div class="catalog-page__grid">
                @foreach ($relatedArtworks as $relatedArtwork)
                    <x-artwork-card :artwork="$relatedArtwork" />
                @endforeach
            </div>
        </section>
    @endif
</x-app>
