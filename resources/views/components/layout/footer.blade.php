{{-- Pie de página del sitio. --}}
<footer class="site-footer">
    <nav aria-label="Navegación del pie">
        <a href="{{ route('shop.index') }}">Tienda</a>
        <a href="{{ route('exhibitions.index') }}">Exposiciones</a>
        {{-- <a href="{{ route('about') }}">Sobre mí</a> --}}
        <a href="{{ route('portfolio') }}">Portfolio</a>
        <a href="{{ route('contact.create') }}">Contacto</a>
        <a href="{{ route('legal.page', ['slug' => 'privacidad']) }}"> Politica & Privacidad</a>
    </nav>
    <p>&copy; {{ date('Y') }} {{ config('app.name', 'Rexalmajestad') }} Todos los derechos reservados.</p>
</footer>
