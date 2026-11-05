@props(['artwork'])

<a class="artwork-card" href="{{ route('artworks.show', ['category' => $artwork->category?->slug, 'slug' => $artwork->slug]) }}">
    <span class="artwork-card__image">
        @if ($artwork->getFirstMediaUrl('artwork'))
            <img src="{{ $artwork->getFirstMediaUrl('artwork', 'webp') }}" alt="{{ $artwork->getFirstMedia('artwork')?->getCustomProperty('alt') ?: $artwork->title.', obra de Rexalmajestad' }}" loading="lazy">
        @else
            <span class="artwork-card__placeholder" role="img" aria-label="Ilustración de muestra pendiente de sustituir por una fotografía de la obra">
                <span aria-hidden="true"></span>
                <span class="visually-hidden">Ilustración ficticia de muestra; falta la fotografía de la obra.</span>
            </span>
        @endif
        @if ($artwork->is_sold)
            <x-artwork-sold class="artwork-card__sold" />
        @endif
    </span>
    <span class="artwork-card__title">{{ $artwork->title }}</span>
    <span class="artwork-card__detail">{{ $artwork->year }} · {{ $artwork->technique }}</span>
</a>
