@php
    $errorCode = $code ?? 404;
    $defaultTitles = [
        403 => 'Acceso denegado',
        404 => 'Página no encontrada',
        419 => 'Página expirada',
        429 => 'Demasiadas solicitudes',
        500 => 'Error en el servidor',
        503 => 'Servicio no disponible',
    ];
    $defaultMessages = [
        403 => 'No tienes permiso para acceder a este espacio.',
        404 => 'La obra o página que buscas no existe o ha sido trasladada.',
        419 => 'La sesión ha expirado por inactividad. Por favor, recarga e inténtalo de nuevo.',
        429 => 'Has realizado demasiadas solicitudes en poco tiempo. Por favor, espera unos instantes.',
        500 => 'Ha ocurrido un problema inesperado en el servidor. Estamos trabajando para solucionarlo.',
        503 => 'Estamos realizando tareas de mantenimiento. Volveremos enseguida.',
    ];
    $displayTitle = $title ?? ($defaultTitles[$errorCode] ?? 'Error');
    $displayMessage = $message ?? ($defaultMessages[$errorCode] ?? 'Ha ocurrido un error inesperado.');
@endphp

<x-app :title="$errorCode . ' · ' . $displayTitle . ' | ' . config('app.name', 'Rexalmajestad')" :noindex="true">
    <div class="error-page">
        <section class="error-page__card" aria-labelledby="error-heading">
            <x-motif-orbit class="error-page__motif" color="cobalt" />
            <span class="error-page__code" aria-hidden="true">{{ $errorCode }}</span>
            <h1 class="error-page__title" id="error-heading">{{ $displayTitle }}</h1>
            <p class="error-page__description">{{ $displayMessage }}</p>

            <div class="error-page__actions">
                <x-link :href="route('home')">Volver al inicio</x-link>
                <x-link :href="route('shop.index')" variant="quiet">Ir a la tienda</x-link>
            </div>
        </section>
    </div>
</x-app>
