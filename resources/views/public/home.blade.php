<x-app
    title="Rexalmajestad | Artista visual"
    description="Óleo y obra original de Rexalmajestad. Explora el portfolio, las exposiciones y la tienda."
    :canonical="route('home')"
>
    <section class="home-page" aria-labelledby="home-title">
        <h1 class="visually-hidden" id="home-title">Rexalmajestad</h1>
        <div class="home-page__orb-stage" data-orb-scene>
            <canvas class="home-page__canvas" aria-hidden="true" data-orb-canvas></canvas>
            <span class="home-page__sphere home-page__sphere--large" aria-hidden="true" data-orb-fallback></span>
            <span class="home-page__sphere home-page__sphere--small" aria-hidden="true" data-orb-fallback></span>
            <button class="home-page__open-menu" type="button" aria-label="Abrir el menú desde la esfera" data-open-menu></button>
        </div>
    </section>
</x-app>