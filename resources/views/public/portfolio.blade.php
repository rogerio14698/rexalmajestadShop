<x-app
    title="Portfolio | Rexalmajestad"
    description="La historia del trabajo de Rexalmajestad, contada por la propia artista con algunas obras clave."
    :canonical="route('portfolio')"
>
    <x-breadcrumbs :items="['Portfolio' => '']" />

    <section class="portfolio-story" aria-labelledby="portfolio-title">
        <header class="portfolio-story__intro" data-reveal>
            <h1 id="portfolio-title">Portfolio</h1>
            <p>La historia detrás de la obra: de dónde vienen las imágenes, qué las une y hacia dónde van. Unas pocas piezas bastan para contarlo.</p>
        </header>

        @forelse ($sections as $section)
            <article class="portfolio-story__section" data-reveal>
                <div class="portfolio-story__text">
                    <h2>{{ $section->heading }}</h2>
                    @if ($section->body)
                        {!! nl2br(e($section->body)) !!}
                    @endif
                </div>
                @if ($section->artwork && $section->artwork->getFirstMediaUrl('artwork'))
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
            <p class="portfolio-story__empty">La historia está en camino. Añade secciones desde el panel de administración.</p>
        @endforelse
    </section>
</x-app>
