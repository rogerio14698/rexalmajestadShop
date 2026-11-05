@props(['href', 'variant' => 'primary'])

<a href="{{ $href }}" {{ $attributes->class(['button', 'button--quiet' => $variant === 'quiet']) }}>
    {{ $slot }}
</a>
