<x-app title="Verificar correo | Rexalmajestad">
    <section class="content-page" aria-labelledby="verify-email-title">
        <h1 id="verify-email-title">Verifica tu correo</h1>
        <p>Antes de acceder a tu cuenta, confirma el enlace que enviamos a tu correo.</p>
        @if (session('status'))<p role="status">{{ session('status') }}</p>@endif
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-button type="submit">Reenviar enlace de verificación</x-button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-button variant="quiet" type="submit">Cerrar sesión</x-button>
        </form>
    </section>
</x-app>
