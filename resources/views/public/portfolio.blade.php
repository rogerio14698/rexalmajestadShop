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

        @forelse ($sections as $section)
            <article class="portfolio-story__section" id="sec-{{ $section->id }}" data-reveal>
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
                        <a href="{{ route('artworks.show', ['category' => $section->artwork->category?->slug, 'slug' => $section->artwork->slug]) }}">
                            <img
                                src="{{ $section->artwork->getFirstMediaUrl('artwork', 'webp') }}"
                                alt="{{ $section->artwork->getFirstMedia('artwork')?->getCustomProperty('alt') ?: $section->artwork->title }}"
                                loading="lazy"
                            >
                        </a>
                        <figcaption>{{ $section->artwork->title }}, {{ $section->artwork->year }} · {{ $section->artwork->technique }}</figcaption>
                    </figure>
                @endif
            </article>
        @empty
            <p class="portfolio-story__empty">La información biográfica y trayectoria se está actualizando. Añade secciones desde el panel de administración.</p>
        @endforelse
    </section>
</x-app>
