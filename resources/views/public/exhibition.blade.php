<x-app
    :title="$exhibition->title.' | Exposiciones | Rexalmajestad'"
    :description="$exhibition->description ?: 'Exposición de Rexalmajestad en '.$exhibition->venue"
    :canonical="route('exhibitions.show', $exhibition->slug)"
>
    @php
        $exhibitionSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $exhibition->title,
            'description' => $exhibition->description,
            'startDate' => $exhibition->starts_at?->toDateString(),
            'endDate' => $exhibition->ends_at?->toDateString(),
            'location' => [
                '@type' => 'Place',
                'name' => $exhibition->venue,
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $exhibition->city,
                    'addressCountry' => $exhibition->country_code,
                ],
            ],
        ];
    @endphp
    @push('structured-data')
        <script type="application/ld+json">{!! json_encode($exhibitionSchema, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
    @endpush

    <x-breadcrumbs :items="[
        'Exposiciones' => route('exhibitions.index'),
        $exhibition->title => '',
    ]" />

    <section class="content-page exhibition-detail" aria-labelledby="exhibition-title">
        @if ($exhibition->getMedia('exhibition')->isNotEmpty())
            <div class="exhibition-detail__gallery">
                @if ($exhibition->getMedia('exhibition')->count() > 1)
                    <x-carousel :images="$exhibition->getMedia('exhibition')" :label="'Galería: '.$exhibition->title" />
                @else
                    @php
                        $singleImage = $exhibition->getFirstMedia('exhibition');
                        $src = $singleImage->getUrl('webp') ?: $singleImage->getUrl();
                        $alt = $singleImage->getCustomProperty('alt') ?: $exhibition->title;
                    @endphp
                    <div class="artwork-carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="{{ $src }}" alt="{{ $alt }}">
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div class="exhibition-detail__information">
            <h1 id="exhibition-title">{{ $exhibition->title }}</h1>
            @if ($exhibition->starts_at)
                <p class="exhibition-detail__dates">{{ $exhibition->starts_at->format('d/m/Y') }}@if ($exhibition->ends_at) – {{ $exhibition->ends_at->format('d/m/Y') }}@endif</p>
            @endif
            @if ($exhibition->published_at)
                <p class="exhibition-detail__published">Publicada el {{ $exhibition->published_at->format('d/m/Y') }}</p>
            @endif
            <p class="exhibition-detail__location">{{ collect([$exhibition->venue, $exhibition->city, $exhibition->country_code])->filter()->join(' · ') }}</p>
            <div class="page-prose">{{ $exhibition->description }}</div>
        </div>
    </section>
</x-app>
