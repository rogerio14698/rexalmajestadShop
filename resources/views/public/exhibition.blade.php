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
    <section class="content-page" aria-labelledby="exhibition-title">
        <h1 id="exhibition-title">{{ $exhibition->title }}</h1>
        @if ($exhibition->starts_at)
            <p>{{ $exhibition->starts_at->format('d/m/Y') }}@if ($exhibition->ends_at) – {{ $exhibition->ends_at->format('d/m/Y') }}@endif</p>
        @endif
        <p>{{ collect([$exhibition->venue, $exhibition->city, $exhibition->country_code])->filter()->join(' · ') }}</p>
        <div class="page-prose">{{ $exhibition->description }}</div>
    </section>

    {{-- Ver si se quiere añadir una galeria de ello --}}
</x-app>
