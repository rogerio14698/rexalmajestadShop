@props(['images', 'label' => 'Galería de obra'])

@php
    $carouselId = 'artwork-gallery-'.\Illuminate\Support\Str::uuid();
    $imageCount = count($images);
@endphp

<div id="{{ $carouselId }}" class="carousel slide artwork-carousel" data-bs-ride="false" aria-label="{{ $label }}">
    @if ($imageCount > 1)
        <div class="carousel-indicators artwork-carousel__indicators">
            @foreach ($images as $index => $image)
                <button
                    type="button"
                    data-bs-target="#{{ $carouselId }}"
                    data-bs-slide-to="{{ $index }}"
                    class="{{ $index === 0 ? 'active' : '' }}"
                    aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                    aria-label="Imagen {{ $index + 1 }}"></button>
            @endforeach
        </div>
    @endif

    <div class="carousel-inner">
        @foreach ($images as $image)
            @php
                $src = is_string($image) ? $image : ($image->getUrl('webp') ?: $image->getUrl());
                $alt = is_string($image) ? '' : $image->getCustomProperty('alt');
            @endphp
            <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                <img src="{{ $src }}" alt="{{ $alt }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
            </div>
        @endforeach
    </div>

    @if ($imageCount > 1)
        <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev" aria-label="Imagen anterior">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next" aria-label="Imagen siguiente">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
    @endif
</div>

@if ($imageCount > 1)
    @pushonce('scripts')
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @endpushonce
@endif
