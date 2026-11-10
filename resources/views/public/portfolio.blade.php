<x-app
    title="Bio & Trayectoria | Rexalmajestad"
    description="Biografía, statement artístico y currículum de Rexalmajestad: exposiciones, premios y trayectoria."
    :canonical="route('portfolio')"
>
    <x-breadcrumbs :items="['Bio' => '']" />

    <section class="portfolio-story" aria-labelledby="portfolio-title">
        <header class="portfolio-story__intro" data-reveal>
            <h1 id="portfolio-title">Biografía & Trayectoria</h1>
            <p>Statement artístico, exposiciones en solitario y colectivas, premios y reconocimientos de Rexalmajestad.</p>
        </header>

        @forelse ($sections as $index => $section)
            @php
                // Alternancia no monótona: varía entre [imagen izquierda], [imagen derecha] y [centrada/asimetrica]
                $layoutVariant = match ($index % 5) {
                    0 => 'portfolio-story__section--text-left',
                    1 => 'portfolio-story__section--image-left',
                    2 => 'portfolio-story__section--image-left portfolio-story__section--offset',
                    3 => 'portfolio-story__section--text-left',
                    4 => 'portfolio-story__section--wide-image',
                };

                $hasVisual = $section->getMedia('gallery')->isNotEmpty() || ($section->artwork && $section->artwork->getFirstMediaUrl('artwork'));

                // Enlace dinámico a la obra asociada o enlace externo opcional
                $targetUrl = null;
                $targetLabel = $section->link_label;
                $isExternal = false;

                if ($section->artwork) {
                    $targetUrl = route('artworks.show', [
                        'category' => $section->artwork->category?->slug ?? 'catalogo',
                        'slug' => $section->artwork->slug,
                    ]);
                    $targetLabel = $targetLabel ?: 'Ver obra «'.$section->artwork->title.'»';
                } elseif ($section->external_url) {
                    $targetUrl = $section->external_url;
                    $targetLabel = $targetLabel ?: 'Visitar enlace';
                    $isExternal = true;
                }
            @endphp
            <article class="portfolio-story__section {{ $layoutVariant }} {{ ! $hasVisual ? 'portfolio-story__section--text-only' : '' }}" id="sec-{{ $section->id }}" data-reveal>
                <div class="portfolio-story__text">
                    @if ($section->year_label)
                        <span class="portfolio-story__year">{{ $section->year_label }}</span>
                    @endif
                    <h2>{{ $section->heading }}</h2>
                    @if ($section->subtitle)
                        <p class="portfolio-story__subtitle">{{ $section->subtitle }}</p>
                    @endif
                    @if ($section->body)
                        <div class="page-prose">
                            {!! nl2br(e($section->body)) !!}
                        </div>
                    @endif

                    @if ($targetUrl && $targetLabel)
                        <div class="portfolio-story__action">
                            <a
                                href="{{ $targetUrl }}"
                                class="portfolio-story__link"
                                @if ($isExternal) target="_blank" rel="noopener noreferrer" @endif
                            >
                                <span>{{ $targetLabel }}</span>
                                <span class="portfolio-story__link-arrow" aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    @endif
                </div>

                @if ($section->getMedia('gallery')->isNotEmpty())
                    <div class="portfolio-story__gallery">
                        @if ($section->getMedia('gallery')->count() > 1)
                            <x-carousel :images="$section->getMedia('gallery')" :label="'Galería: '.$section->heading" />
                        @else
                            @php
                                $single = $section->getFirstMedia('gallery');
                                $src = $single->getUrl('webp') ?: $single->getUrl();
                                $alt = $single->getCustomProperty('alt') ?: $section->heading;
                            @endphp
                            <div class="artwork-carousel">
                                <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @elseif ($section->artwork && $section->artwork->getFirstMediaUrl('artwork'))
                    <figure class="portfolio-story__figure">
                        <a href="{{ $targetUrl ?: route('artworks.show', ['category' => $section->artwork->category?->slug, 'slug' => $section->artwork->slug]) }}">
                            <img
                                src="{{ $section->artwork->getFirstMediaUrl('artwork', 'webp') }}"
                                alt="{{ $section->artwork->getFirstMedia('artwork')?->getCustomProperty('alt') ?: $section->artwork->title }}"
                                loading="lazy"
                            >
                        </a>
                        <figcaption>
                            {{ $section->artwork->title }}, {{ $section->artwork->year }} · {{ $section->artwork->technique }}
                        </figcaption>
                    </figure>
                @endif
            </article>
        @empty
            <p class="portfolio-story__empty">La información biográfica y trayectoria se está actualizando. Añade secciones desde el panel de administración.</p>
        @endforelse
    </section>
</x-app>
