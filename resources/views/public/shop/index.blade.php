@php
    $customHeroUrl = \App\Models\SiteSetting::getHeroImageUrl('hero_shop');
    $heroBackdropUrl = $customHeroUrl ?: ($artworks->first(fn ($artwork) => $artwork->getFirstMediaUrl('artwork') !== '')?->getFirstMediaUrl('artwork', 'webp') ?: '');
    $priceFor = fn ($artwork) => number_format($artwork->price_cents / 100, 0, ',', '.').' €';
@endphp

<x-app
    title="Tienda | Rexalmajestad"
    description="Lista de obra disponible de Rexalmajestad: óleos, tintas, acuarelas y grabados originales."
    :canonical="route('shop.index')"
>
    <section class="shop-page" aria-labelledby="shop-title">
        {{-- La hoja: la lista de obra del estudio, como el inventario impreso de una exposición. --}}
        <header class="shop-hero">
            @if ($heroBackdropUrl)
                <img
                    class="shop-hero__backdrop"
                    src="{{ $heroBackdropUrl }}"
                    alt=""
                    aria-hidden="true"
                >
            @endif
            <div class="shop-hero__panel" data-reveal>
                <h1 id="shop-title">Lista de obra</h1>
                <p class="shop-hero__count">{{ $artworks->total() }} {{ $artworks->total() === 1 ? 'obra' : 'obras' }} · piezas únicas</p>
                <p class="shop-hero__lede">Pintura y obra sobre papel de Rexalmajestad, en venta directa desde el estudio. Cada pieza sale firmada y con certificado. Cuando una se vende, desaparece de esta hoja.</p>
                <nav class="shop-hero__filters" aria-label="Filtrar por técnica">
                    <a href="{{ route('shop.index') }}" @if (! $selectedCategory) aria-current="page" @endif>Todas</a>
                    @foreach ($categories as $filterCategory)
                        <a
                            href="{{ route('shop.index', ['category' => $filterCategory->slug]) }}"
                            @if ($selectedCategory && $selectedCategory->is($filterCategory)) aria-current="page" @endif
                        >{{ $filterCategory->name }}</a>
                    @endforeach
                </nav>
                <form class="shop-hero__search" method="GET" action="{{ route('shop.index') }}" role="search">
                    @if ($selectedCategory)
                        <input type="hidden" name="category" value="{{ $selectedCategory->slug }}">
                    @endif
                    <label class="visually-hidden" for="shop-search">Buscar obra</label>
                    <input
                        id="shop-search"
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Buscar por nombre, año o material…"
                        autocomplete="off"
                    >
                    <button type="submit">Buscar</button>
                </form>
                @if ($search !== '')
                    <p class="shop-hero__searching">
                        Resultados para «{{ $search }}» ·
                        <a href="{{ route('shop.index', array_filter(['category' => $selectedCategory?->slug])) }}">Quitar búsqueda</a>
                    </p>
                @endif
            </div>
        </header>

        <div class="shop-sheet">
            @forelse ($categories as $sheetCategory)
                @continue($selectedCategory && ! $selectedCategory->is($sheetCategory))
                @php $categoryArtworks = $artworks->where('category_id', $sheetCategory->getKey()); @endphp
                @continue($categoryArtworks->isEmpty())

                <section class="shop-sheet__group" aria-labelledby="group-{{ $sheetCategory->slug }}">
                    <h2 id="group-{{ $sheetCategory->slug }}">{{ $sheetCategory->name }}</h2>
                    @php $plate = $categoryArtworks->first(fn ($artwork) => $artwork->getFirstMediaUrl('artwork') !== ''); @endphp
                    @if ($plate)
                        {{-- Lámina destacada de la técnica, como las láminas pegadas en un dosier. --}}
                        {{-- <figure class="shop-plate" data-reveal>
                            <a href="{{ route('artworks.show', ['category' => $plate->category?->slug, 'slug' => $plate->slug]) }}">
                                <img
                                    src="{{ $plate->getFirstMediaUrl('artwork', 'webp') }}"
                                    alt="{{ $plate->getFirstMedia('artwork')?->getCustomProperty('alt') ?: $plate->title }}"
                                    loading="lazy"
                                >
                            </a>
                            <figcaption>{{ $plate->title }}, {{ $plate->year }} · {{ $plate->technique }}</figcaption>
                        </figure> --}}
                    @endif
                    <ol class="shop-list">
                        @foreach ($categoryArtworks as $artwork)
                            <li class="shop-row" data-reveal>
                                <a class="shop-row__link" href="{{ route('artworks.show', ['category' => $artwork->category?->slug, 'slug' => $artwork->slug]) }}">
                                    <span class="shop-row__media">
                                        @if ($artwork->getFirstMediaUrl('artwork'))
                                            <img
                                                src="{{ $artwork->getFirstMediaUrl('artwork', 'webp') }}"
                                                alt="{{ $artwork->getFirstMedia('artwork')?->getCustomProperty('alt') ?: $artwork->title }}"
                                                loading="lazy"
                                            >
                                        @endif
                                    </span>
                                    <span class="shop-row__heading">
                                        <span class="shop-row__title">{{ $artwork->title }}</span>
                                        <span class="shop-row__meta">{{ $artwork->year }} · {{ $artwork->technique }}</span>
                                        @if ($artwork->is_sold)
                                            <span class="shop-row__badge shop-row__badge--sold">Vendida</span>
                                        @else
                                            <span class="shop-row__badge shop-row__badge--stock">En stock</span>
                                        @endif
                                    </span>
                                    <span class="shop-row__dims">
                                        @if ($artwork->height_cm && $artwork->width_cm)
                                            {{ rtrim(rtrim(number_format((float) $artwork->height_cm, 1, ',', ''), '0'), ',') }} × {{ rtrim(rtrim(number_format((float) $artwork->width_cm, 1, ',', ''), '0'), ',') }} cm
                                        @endif
                                        @if ($artwork->gallery)
                                            <span class="shop-row__series">Colección {{ $artwork->gallery->name }}</span>
                                        @endif
                                    </span>
                                    <span class="shop-row__price">
                                        @if ($artwork->is_sold)
                                            {{-- La etiqueta «Vendida» ya lo indica junto al título. --}}
                                        @elseif ($artwork->ask_shipping)
                                            <span class="shop-row__state shop-row__state--ask">Consultar envío</span>
                                        @else
                                            {{ $priceFor($artwork) }}
                                        @endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @empty
                <p class="shop-sheet__empty">Ahora mismo no hay obra disponible. Vuelve pronto o escríbeme.</p>
            @endforelse

            @if ($artworks->isEmpty())
                <p class="shop-sheet__empty">No hay obras publicadas en esta selección.</p>
            @endif

            @if ($artworks->hasPages())
                <nav class="shop-pagination" aria-label="Paginación de la lista de obra">
                    @if ($artworks->onFirstPage())
                        <span class="shop-pagination__link is-disabled" aria-disabled="true">← Anterior</span>
                    @else
                        <a class="shop-pagination__link" href="{{ $artworks->previousPageUrl() }}" rel="prev">← Anterior</a>
                    @endif
                    <span class="shop-pagination__status">Página {{ $artworks->currentPage() }} de {{ $artworks->lastPage() }}</span>
                    @if ($artworks->hasMorePages())
                        <a class="shop-pagination__link" href="{{ $artworks->nextPageUrl() }}" rel="next">Siguiente →</a>
                    @else
                        <span class="shop-pagination__link is-disabled" aria-disabled="true">Siguiente →</span>
                    @endif
                </nav>
            @endif
        </div>

        {{-- La tarjeta de respuesta. --}}
        <aside class="shop-reply" aria-labelledby="shop-reply-title" data-reveal>
            <div class="shop-reply__card">
                <h2 id="shop-reply-title">¿Te interesa una pieza?</h2>
                <p>Escríbeme para reservar una obra, pedir más fotografías o preparar un envío a tu ciudad.</p>
                <a class="button" href="{{ route('contact.create') }}">Escribir al estudio</a>
            </div>
        </aside>
    </section>
</x-app>
