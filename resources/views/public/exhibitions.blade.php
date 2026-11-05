@php
    $today = now()->startOfDay();
    $byYear = $exhibitions->groupBy(fn ($exhibition) => $exhibition->starts_at?->year ?? 'Sin fecha');
    $statusFor = function ($exhibition) use ($today) {
        if ($exhibition->starts_at === null) {
            return null;
        }

        if ($exhibition->starts_at->gt($today)) {
            return 'Próximamente';
        }

        if ($exhibition->ends_at?->gte($today)) {
            return 'En curso';
        }

        return null;
    };
@endphp

<x-app title="Exposiciones | Rexalmajestad" description="Exposiciones y muestras de la obra de Rexalmajestad." :canonical="route('exhibitions.index')">
    {{-- El registro de salas: la trayectoria expositiva como ficha cronológica del estudio. --}}
    <section class="exhibitions-page" aria-labelledby="exhibitions-title">
        <header class="exhibitions-hero" data-reveal>
            <h1 id="exhibitions-title">Exposiciones</h1>
            <p class="exhibitions-hero__lede">El registro de salas del estudio: muestras individuales y colectivas donde ha colgado la obra de Rexalmajestad, de la más reciente a la primera.</p>
            <p class="exhibitions-hero__count">{{ $exhibitions->count() }} {{ $exhibitions->count() === 1 ? 'muestra registrada' : 'muestras registradas' }}</p>
        </header>

        <div class="exhibitions-record">
            @forelse ($byYear as $year => $yearExhibitions)
                <section class="exhibitions-year" aria-labelledby="exh-year-{{ \Illuminate\Support\Str::slug($year) }}">
                    <h2 class="exhibitions-year__number" id="exh-year-{{ \Illuminate\Support\Str::slug($year) }}">{{ $year }}</h2>
                    <ol class="exhibitions-year__list">
                        @foreach ($yearExhibitions as $exhibition)
                            <li class="exhibition-entry" data-reveal>
                                <a class="exhibition-entry__link" href="{{ route('exhibitions.show', $exhibition->slug) }}">
                                    <span class="exhibition-entry__dates">
                                        {{ $exhibition->starts_at?->format('d/m') }}@if ($exhibition->ends_at) — {{ $exhibition->ends_at->format('d/m') }}@endif
                                    </span>
                                    <span class="exhibition-entry__heading">
                                        <span class="exhibition-entry__title">{{ $exhibition->title }}</span>
                                        <span class="exhibition-entry__venue">{{ collect([$exhibition->venue, $exhibition->city, $exhibition->country_code])->filter()->join(' · ') }}</span>
                                    </span>
                                    @if ($statusFor($exhibition))
                                        <span class="exhibition-entry__status">{{ $statusFor($exhibition) }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @empty
                <p class="exhibitions-empty">Las próximas exposiciones se anunciarán aquí.</p>
            @endforelse
        </div>

        <aside class="exhibitions-invite" aria-labelledby="exhibitions-invite-title" data-reveal>
            <div class="exhibitions-invite__card">
                <h2 id="exhibitions-invite-title">¿Quieres exponer la obra?</h2>
                <p>Si diriges una sala o preparas una muestra colectiva, escríbeme y hablamos de fechas y de piezas disponibles.</p>
                <a class="button" href="{{ route('contact.create') }}">Escribir al estudio</a>
            </div>
        </aside>
    </section>
</x-app>
