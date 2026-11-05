<x-app title="Recuperar contraseña | Rexalmajestad">
    <section class="content-page" aria-labelledby="forgot-password-title">
        <h1 id="forgot-password-title">Recuperar contraseña</h1>
        <p>Indica tu correo y, si existe una cuenta, recibirás un enlace de restablecimiento.</p>
        @if (session('status'))<p role="status">{{ session('status') }}</p>@endif
        <form method="POST" action="{{ route('password.email') }}" class="checkout-form">
            @csrf
            <x-form-field name="email" label="Correo electrónico" type="email" required autocomplete="username" />
            @error('email')<p role="alert">{{ $message }}</p>@enderror
            <x-button type="submit">Enviar enlace</x-button>
        </form>
    </section>
</x-app>
