<x-app title="Crear cuenta |Rexalmajestad">
    <section class="content-page" aria-labelledby="register-title">
        <h1 id="register-title">Crear cuenta</h1>
        <form method="POST" action="{{ route('register.store') }}" class="checkout-form">
            @csrf
            <x-form-field name="name" label="Nombre" required autocomplete="name" />
            <x-form-field name="email" label="Correo electrónico" type="email" required autocomplete="username" />
            <x-form-field name="password" label="Contraseña (mínimo 12 caracteres, letras y números)" type="password" required autocomplete="new-password" />
            <x-form-field name="password_confirmation" label="Repite la contraseña" type="password" required autocomplete="new-password" />
            <x-button type="submit">Crear cuenta</x-button>
        </form>
    </section>
</x-app>
