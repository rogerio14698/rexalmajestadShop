@props(['items' => []])

@if (!empty($items))
    <nav class="site-breadcrumbs" aria-label="Miga de pan">
        <ol>
            <li><a href="{{ route('home') }}">Inicio</a></li>
            @foreach ($items as $label => $url)
                @if ($loop->last || empty($url) || is_numeric($label))
                    <li aria-current="page"><span>{{ is_numeric($label) ? $url : $label }}</span></li>
                @else
                    <li><a href="{{ $url }}">{{ $label }}</a></li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif
