@php
    $groupedArtworks = $artworks->groupBy('year');
    $title = $selectedCategory->name;
@endphp

<x-app :title="$title.' | Rexalmajestad'" :description="'Obras de '.$title.' de Rexalmajestad.'" :canonical="url()->current()">
    <section class="catalog-page" aria-labelledby="catalog-title">
        <div class="catalog-page__intro">
            <div>
                <h1 id="catalog-title">{{ $title }}</h1>
                <p>Obra original seleccionada de Rexalmajestad.</p>
            </div>
            <a class="text-link" href="{{ route('shop.index', ['category' => $selectedCategory->slug]) }}">Ver obras disponibles</a>
        </div>

        @forelse ($groupedArtworks as $year => $yearArtworks)
            <section aria-labelledby="year-{{ $year }}">
                <x-year-title :year="$year" id="year-{{ $year }}" />
                <div class="catalog-page__grid">
                    @foreach ($yearArtworks as $artwork)
                        <x-artwork-card :artwork="$artwork" />
                    @endforeach
                </div>
            </section>
        @empty
            <p>No hay obras publicadas en esta selección.</p>
        @endforelse
    </section>
</x-app>