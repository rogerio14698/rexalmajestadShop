@props(['breadcrumbs' => []])

@php
    $currentRoute = request()->route()?->getName();
@endphp

@if (!empty($breadcrumbs))
    <nav class="account-breadcrumbs" aria-label="Miga de pan">
        <ol>
            <li><a href="{{ route('home') }}">Inicio</a></li>
            <li><a href="{{ route('account.dashboard') }}">Mi cuenta</a></li>
            @foreach ($breadcrumbs as $label => $url)
                @if ($loop->last && (is_numeric($label) || empty($url)))
                    <li aria-current="page"><span>{{ is_numeric($label) ? $url : $label }}</span></li>
                @else
                    <li><a href="{{ $url }}">{{ $label }}</a></li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif

<nav class="account-public-nav" aria-label="Navegación al sitio público">
    <span class="account-public-nav__label">Explorar sitio:</span>
    <a href="{{ route('shop.index') }}">Tienda</a>
    <a href="{{ route('portfolio') }}">Portfolio</a>
    <a href="{{ route('exhibitions.index') }}">Exposiciones</a>
    <a href="{{ route('about') }}">Sobre mí</a>
    <a href="{{ route('contact.create') }}">Contacto</a>
    <a href="{{ route('cart.show') }}">Carrito</a>
</nav>

<nav class="account-nav" aria-label="Secciones de cuenta">
    <a href="{{ route('account.dashboard') }}" @if ($currentRoute === 'account.dashboard') aria-current="page" @endif>Panel</a>
    <a href="{{ route('account.orders') }}" @if (str_starts_with($currentRoute ?? '', 'account.orders')) aria-current="page" @endif>Pedidos</a>
    <a href="{{ route('account.favorites') }}" @if ($currentRoute === 'account.favorites') aria-current="page" @endif>Favoritos</a>
    <a href="{{ route('account.invoices') }}" @if ($currentRoute === 'account.invoices') aria-current="page" @endif>Facturas</a>
    <a href="{{ route('account.addresses') }}" @if (str_starts_with($currentRoute ?? '', 'account.addresses')) aria-current="page" @endif>Direcciones</a>
    <a href="{{ route('profile.edit') }}" @if ($currentRoute === 'profile.edit') aria-current="page" @endif>Perfil y privacidad</a>
</nav>
