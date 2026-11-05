@props(['images', 'label' => 'Galería de obra'])

@php
    $carouselId = 'artwork-gallery-'.\Illuminate\Support\Str::uuid();
@endphp

<div id="{{ $carouselId }}" class="carousel slide artwork-carousel" data-bs-ride="carousel" aria-label="{{ $label }}">
    <div class="carousel-inner">
        @foreach ($images as $image)
            @php
                $src = is_string($image) ? $image : $image->getUrl('webp');
                $alt = is_string($image) ? '' : $image->getCustomProperty('alt');
            @endphp
            <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                <img src="{{ $src }}" alt="{{ $alt }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
            </div>
        @endforeach
    </div>
    @if (count($images) > 1)
        <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev" aria-label="Imagen anterior">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next" aria-label="Imagen siguiente">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
    @endif
</div>
