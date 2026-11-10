{{-- Pie de página del sitio. --}}
@php
    $socialLinks = \App\Models\SocialLink::query()->active()->get();
    $legalDocuments = \App\Models\LegalDocument::query()->published()->orderBy('id')->get();
@endphp
<footer class="site-footer">
    <nav aria-label="Navegación del pie">
        <a href="{{ route('shop.index') }}">Tienda</a>
        <a href="{{ route('portfolio') }}">Bio</a>
        <a href="{{ route('exhibitions.index') }}">Exposiciones</a>
        <a href="{{ route('contact.create') }}">Contacto</a>
        @if ($legalDocuments->isNotEmpty())
            @foreach ($legalDocuments as $doc)
                <a href="{{ route('legal.page', ['slug' => $doc->slug]) }}">{{ $doc->title }}</a>
            @endforeach
        @else
            <a href="{{ route('legal.page', ['slug' => 'aviso-legal']) }}">Aviso Legal</a>
            <a href="{{ route('legal.page', ['slug' => 'privacidad']) }}">Política de Privacidad</a>
            <a href="{{ route('legal.page', ['slug' => 'cookies']) }}">Cookies</a>
        @endif
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
