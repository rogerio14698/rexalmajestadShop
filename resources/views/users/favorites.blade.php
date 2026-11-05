<x-app title="Mis favoritos | Rexalmajestad">
    <section class="account-page" aria-labelledby="favorites-title">
        <header>
            <h1 id="favorites-title">Mis favoritos</h1>
        </header>

        <x-account-navigation :breadcrumbs="['Mis favoritos' => '']" />

        @if ($artworks->isEmpty())
            <p>Aún no has guardado obras favoritas.</p>
        @else
            <div class="catalog-page__grid">
                @foreach ($artworks as $artwork)
                    <x-artwork-card :artwork="$artwork" />
                @endforeach
            </div>
            {{ $artworks->links() }}
        @endif
    </section>
</x-app>
