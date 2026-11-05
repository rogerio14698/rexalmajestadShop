@props(['color' => 'cobalt'])

@php($stroke = $color === 'bone' ? 'var(--color-bone-light)' : 'var(--color-cobalt)')

<svg {{ $attributes->class(['motif', 'motif--quiet']) }} viewBox="0 0 500 500" fill="none" aria-hidden="true">
    <ellipse cx="250" cy="250" rx="218" ry="78" transform="rotate(-32 250 250)" stroke="{{ $stroke }}" stroke-width="1.2" />
    <ellipse cx="250" cy="250" rx="218" ry="78" transform="rotate(32 250 250)" stroke="{{ $stroke }}" stroke-width="1.2" />
    <circle cx="250" cy="250" r="118" stroke="{{ $stroke }}" stroke-width="0.8" />
    <circle cx="414" cy="147" r="5" fill="{{ $stroke }}" />
    <circle cx="84" cy="353" r="4" fill="{{ $stroke }}" />
</svg>
