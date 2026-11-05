@props(['color' => 'cobalt'])

@php($stroke = $color === 'bone' ? 'var(--color-bone-light)' : 'var(--color-cobalt)')

<svg {{ $attributes->class(['motif', 'motif--quiet']) }} viewBox="0 0 120 120" fill="none" aria-hidden="true">
    <path d="M10 10H110V110H10zM10 60H110M60 10V110" stroke="{{ $stroke }}" stroke-width="1" />
    <circle cx="60" cy="60" r="7" fill="{{ $stroke }}" />
</svg>
