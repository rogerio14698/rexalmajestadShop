{{--
    Layout principal del sitio. Todas las páginas se montan sobre este archivo
    usando <x-app title="..." description="...">...</x-app>.

    Piezas editables:
    - Menú overlay:   resources/views/components/layout/menu.blade.php
    - Enlaces del nav: resources/views/components/layout/nav.blade.php
    - Footer:          resources/views/components/layout/footer.blade.php
--}}
@props([
    'title' => 'Rexalmajestad | Artista visual',
    'description' => 'Obra original de Rexalmajestad: óleo, dibujo, grabado, acuarela y cerámica.',
    'canonical' => null,
    'noindex' => false,
])

@php
    $privatePage = request()->is('admin*', 'cuenta*', 'carrito', 'checkout*', 'perfil', 'iniciar-sesion', 'registro', 'recuperar-contrasena*', 'restablecer-contrasena*', 'verificar-correo*');
    $siteSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => 'Rexalmajestad',
        'url' => route('home'),
        'sameAs' => ['https://www.instagram.com/rexalmajestad/'],
    ];
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <x-seo.meta :title="$title" :description="$description" :canonical="$canonical ?? request()->url()" :noindex="$noindex || $privatePage" />
        <script type="application/ld+json">{!! json_encode($siteSchema, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=DM+Sans:wght@400;500;600;700&display=swap&subset=latin,latin-ext" rel="stylesheet">
        @vite(['resources/scss/app.scss', 'resources/js/app.js'])
        @stack('styles')
        @stack('head')
        @stack('structured-data')
    </head>
    <body>
        <a class="skip-link" href="#contenido">Saltar al contenido</a>

        <x-layout.menu />

        <button class="site-menu-trigger" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="site-menu" data-menu-trigger>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </button>

        <div class="site-shell" data-menu-background>
            <main class="site-main" id="contenido" tabindex="-1">
                {{ $slot }}
            </main>
            {{-- Banner para las cookies notice; ver como hacer despues actualizar esto. --}}
            <x-cookie-notice />
            
            <x-layout.footer />
        </div>
        @stack('scripts')
    </body>
</html>
