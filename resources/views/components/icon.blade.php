@props(['name', 'label' => null])

<svg {{ $attributes->merge(['viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.7', 'aria-hidden' => $label ? 'false' : 'true', 'role' => $label ? 'img' : 'presentation']) }}>
    @if ($label)
        <title>{{ $label }}</title>
    @endif
    @switch($name)
        @case('arrow')
            <path d="M4 12h15m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
            @break
        @case('bag')
            <path d="M5 8h14l1 13H4L5 8Z" stroke-linejoin="round" />
            <path d="M9 9V6a3 3 0 0 1 6 0v3" stroke-linecap="round" />
            @break
        @case('close')
            <path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" />
            @break
        @default
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>
