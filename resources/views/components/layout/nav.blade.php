{{-- Navegación principal. Las categorías y subcategorías se gestionan desde el admin (/admin/categories). --}}
@php
    $parentCategories = \Illuminate\Support\Facades\Schema::hasTable('categories')
        ? \App\Models\Category::query()->whereNull('parent_id')->with('children')->orderBy('position')->orderBy('name')->get()
        : collect();
    $bioSections = \Illuminate\Support\Facades\Schema::hasTable('portfolio_sections')
        ? \App\Models\PortfolioSection::query()->published()->orderBy('position')->get()
        : collect();
@endphp
<nav class="site-menu__nav" aria-label="Navegación principal">
    <ul class="site-menu__accordion">
        {{-- Categorías principales y subcategorías dinámicas --}}
        @foreach ($parentCategories as $parentCat)
            @if ($parentCat->children->isNotEmpty())
                <li class="site-menu__item site-menu__item--has-children">
                    <details class="site-menu__details" @if (request()->is('obras/'.$parentCat->slug.'*')) open @endif>
                        <summary class="site-menu__summary">
                            <span>{{ $parentCat->name }}</span>
                            <span class="site-menu__indicator" aria-hidden="true">+</span>
                        </summary>
                        <ul class="site-menu__sublist">
                            <li>
                                <a href="{{ route('artworks.category', ['category' => $parentCat->slug]) }}"
                                    class="site-menu__sublink {{ request()->is('obras/'.$parentCat->slug) ? 'is-active' : '' }}">
                                    Ver todo {{ $parentCat->name }}
                                </a>
                            </li>
                            @foreach ($parentCat->children as $childCat)
                                <li>
                                    <a href="{{ route('artworks.category', ['category' => $childCat->slug]) }}"
                                        class="site-menu__sublink {{ request()->is('obras/'.$childCat->slug) ? 'is-active' : '' }}">
                                        {{ $childCat->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                </li>
            @else
                <li class="site-menu__item">
                    <a href="{{ route('artworks.category', ['category' => $parentCat->slug]) }}"
                        class="site-menu__link {{ request()->is('obras/'.$parentCat->slug) ? 'is-active' : '' }}">
                        {{ $parentCat->name }}
                    </a>
                </li>
            @endif
        @endforeach

        {{-- Sección Bio con desglose dinámico en acordeón --}}
        <li class="site-menu__item site-menu__item--has-children">
            <details class="site-menu__details" @if (request()->routeIs('portfolio')) open @endif>
                <summary class="site-menu__summary">
                    <span>Bio</span>
                    <span class="site-menu__indicator" aria-hidden="true">+</span>
                </summary>
                <ul class="site-menu__sublist">
                    <li>
                        <a href="{{ route('portfolio') }}" class="site-menu__sublink">
                            Biografía & Statement
                        </a>
                    </li>
                    @if ($bioSections->isNotEmpty())
                        @foreach ($bioSections as $bSec)
                            <li>
                                <a href="{{ route('portfolio') }}#sec-{{ $bSec->id }}" class="site-menu__sublink">
                                    {{ $bSec->heading }}
                                </a>
                            </li>
                        @endforeach
                    @else
                        <li>
                            <a href="{{ route('portfolio') }}" class="site-menu__sublink">
                                Currículum & Trayectoria
                            </a>
                        </li>
                    @endif
                </ul>
            </details>
        </li>

        {{-- Enlaces directos --}}
        <li class="site-menu__item">
            <a href="{{ route('contact.create') }}" class="site-menu__link {{ request()->routeIs('contact.create') ? 'is-active' : '' }}">
                Contacto
            </a>
        </li>
        <li class="site-menu__item">
            <a href="{{ route('shop.index') }}" class="site-menu__link {{ request()->routeIs('shop.index') ? 'is-active' : '' }}">
                Tienda
            </a>
        </li>
        <li class="site-menu__item">
            <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" class="site-menu__link">
                {{ auth()->check() ? 'Mi cuenta' : 'Login' }}
            </a>
        </li>
       
        <li class="site-menu__item site-menu__item--cart">
            <a href="{{ route('cart.show') }}" class="site-menu__link">
                Carrito
            </a>
        </li>
         @auth
            <li class="site-menu__item">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="site-menu__logout-btn" type="submit">Cerrar sesión</button>
                </form>
            </li>
        @endauth
    </ul>
</nav>
