<x-app title="Restablecer contraseña | Rexalmajestad">
    <section class="content-page" aria-labelledby="reset-password-title">
        <h1 id="reset-password-title">Restablecer contraseña</h1>
        <form method="POST" action="{{ route('password.store') }}" class="checkout-form">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-form-field name="email" label="Correo electrónico" type="email" required autocomplete="username" />
            <x-form-field name="password" label="Nueva contraseña" type="password" required autocomplete="new-password" />
            <x-form-field name="password_confirmation" label="Repite la contraseña" type="password" required autocomplete="new-password" />
            @error('email')<p role="alert">{{ $message }}</p>@enderror
            <x-button type="submit">Guardar contraseña</x-button>
        </form>
    </section>
</x-app>
