{{-- Pie de página del sitio. --}}
@php
    $socialLinks = \App\Models\SocialLink::query()->active()->get();
@endphp
<footer class="site-footer">
    <nav aria-label="Navegación del pie">
        <a href="{{ route('shop.index') }}">Tienda</a>
        <a href="{{ route('portfolio') }}">Bio</a>
        <a href="{{ route('exhibitions.index') }}">Exposiciones</a>
        <a href="{{ route('contact.create') }}">Contacto</a>
        <a href="{{ route('legal.page', ['slug' => 'privacidad']) }}">Politica & Privacidad</a>
    </nav>
    <div class="enlacesRedes">
        @if ($socialLinks->isNotEmpty())
            <ul>
                @foreach ($socialLinks as $link)
                    <li>
                        <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer">
                            <span>{{ $link->name }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Rexalmajestad') }} Todos los derechos reservados.</p>
    </div>
</footer>
