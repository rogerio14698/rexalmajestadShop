{{-- Botón «Continuar con Google» mostrado bajo el formulario de login del panel de administración. --}}
<div style="margin-top: 1rem; display: grid; gap: 0.75rem;">
    <div style="display: flex; align-items: center; gap: 0.75rem; color: rgb(113 113 122); font-size: 0.875rem;">
        <span style="flex: 1; height: 1px; background: rgb(228 228 231);"></span>
        <span>o</span>
        <span style="flex: 1; height: 1px; background: rgb(228 228 231);"></span>
    </div>

    <x-filament::button
        tag="a"
        href="{{ url('/auth/google/redirect') }}"
        color="gray"
        outlined
        icon-position="before"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" style="width: 1.1rem; height: 1.1rem; margin-right: 0.5rem;" aria-hidden="true">
            <path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3l5.7-5.7C34.5 6.1 29.5 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.3-.1-2.6-.4-3.9z"/>
            <path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.9 1.2 8 3l5.7-5.7C34.5 6.1 29.5 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>
            <path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/>
            <path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C41.4 35.1 44 30 44 24c0-1.3-.1-2.6-.4-3.9z"/>
        </svg>
        Continuar con Google
    </x-filament::button>
</div>
