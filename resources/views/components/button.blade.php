@props(['variant' => 'primary', 'type' => 'button'])

<button type="{{ $type }}" {{ $attributes->class(['button', 'button--quiet' => $variant === 'quiet']) }}>
    {{ $slot }}
</button>
