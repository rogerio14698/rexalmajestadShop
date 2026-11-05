<x-app title="Iniciar sesión | Rexalmajestad">
    <section class="content-page" aria-labelledby="login-title">
        <h1 id="login-title">Iniciar sesión</h1>
        @if (session('status'))<p role="status">{{ session('status') }}</p>@endif
        <form method="POST" action="{{ route('login.store') }}" class="checkout-form">
            @csrf
            <x-form-field name="email" label="Correo electrónico" type="email" required autocomplete="username" />
            <x-form-field name="password" label="Contraseña" type="password" required autocomplete="current-password" />
            <label><input type="checkbox" name="remember" value="1"> Mantener la sesión iniciada</label>
            @error('email')<p role="alert">{{ $message }}</p>@enderror
            <x-button type="submit">Entrar</x-button>
        </form>
        <p><a class="text-link" href="{{ route('password.request') }}">¿Olvidaste la contraseña?</a></p>
        <p><a class="text-link" href="{{ route('register') }}">Crear una cuenta</a></p>
    </section>
</x-app>
