@props(['color' => 'cobalt', 'density' => 8, 'seed' => 1])

@php
    $stroke = $color === 'bone' ? 'var(--color-bone-light)' : 'var(--color-cobalt)';
    $steps = max(4, min((int) $density, 20));
    $height = 160;
    $points = [];

    for ($index = 0; $index <= $steps; $index++) {
        $progress = $index / $steps;
        $y = $progress * $height;
        $x = 50 + sin(($progress * 2 * M_PI) + $seed) * 28;
        $oppositeX = 100 - $x;
        $points[] = compact('x', 'oppositeX', 'y');
    }

    $pathA = 'M '.$points[0]['x'].' '.$points[0]['y'];
    $pathB = 'M '.$points[0]['oppositeX'].' '.$points[0]['y'];
    foreach (array_slice($points, 1) as $point) {
        $pathA .= ' L '.$point['x'].' '.$point['y'];
        $pathB .= ' L '.$point['oppositeX'].' '.$point['y'];
    }
@endphp

<svg {{ $attributes->class(['motif', 'motif--quiet']) }} viewBox="0 0 100 160" fill="none" aria-hidden="true">
    <path d="{{ $pathA }}" stroke="{{ $stroke }}" stroke-width="1.5" stroke-linecap="round" />
    <path d="{{ $pathB }}" stroke="{{ $stroke }}" stroke-width="1.5" stroke-linecap="round" />
    @foreach ($points as $point)
        <line x1="{{ $point['x'] }}" y1="{{ $point['y'] }}" x2="{{ $point['oppositeX'] }}" y2="{{ $point['y'] }}" stroke="{{ $stroke }}" stroke-width="0.8" />
    @endforeach
</svg>
