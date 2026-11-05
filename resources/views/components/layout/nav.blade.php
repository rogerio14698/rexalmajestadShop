{{-- Navegación principal. Las categorías se gestionan desde el admin (/admin). --}}
@php
    $menuCategories = \Illuminate\Support\Facades\Schema::hasTable('categories')
        ? \App\Models\Category::query()->orderBy('position')->orderBy('name')->get()
        : collect();
@endphp
<nav class="site-menu__links" aria-label="Navegación principal">
    @foreach ($menuCategories as $menuCategory)
        <a href="{{ route('artworks.category', ['category' => $menuCategory->slug]) }}" data-menu-item>{{ $menuCategory->name }}</a>
    @endforeach
    <a href="{{ route('shop.index') }}" data-menu-item>Tienda</a>
    <a href="{{ route('portfolio') }}" data-menu-item>Portfolio</a>
    <a href="{{ route('exhibitions.index') }}" data-menu-item>Exposiciones</a>
    <a href="{{ route('about') }}" data-menu-item>Sobre mí</a>
    <a href="{{ route('contact.create') }}" data-menu-item>Contacto</a>
    <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" data-menu-item>{{ auth()->check() ? 'Mi cuenta' : 'Iniciar sesión' }}</a>
    @auth
        <form method="POST" action="{{ route('logout') }}" data-menu-item>
            @csrf
            <button class="site-menu__link" type="submit">Cerrar sesión</button>
        </form>
    @endauth
    <a href="{{ route('cart.show') }}" data-menu-item>Carrito</a>
</nav>
