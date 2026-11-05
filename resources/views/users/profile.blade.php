<x-app title="Perfil y privacidad | Rexalmajestad">
    <section class="account-page" aria-labelledby="profile-title">
        <header>
            <h1 id="profile-title">Perfil y privacidad</h1>
        </header>

        <x-account-navigation :breadcrumbs="['Perfil y privacidad' => '']" />

        @if (session('status'))
            <p class="account-status" role="status">{{ session('status') }}</p>
        @endif

        <div class="account-privacy-box">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')
                <x-form-field name="name" label="Nombre" required autocomplete="name" :value="$user->name" />
                <x-form-field name="email" label="Correo electrónico" type="email" required autocomplete="email" :value="$user->email" />
                <x-button type="submit">Guardar perfil</x-button>
            </form>
        </div>

        <p><a class="text-link" href="{{ route('account.export') }}">Descargar mis datos personales</a></p>

        <div class="account-privacy-box">
            <h2>Eliminar cuenta</h2>
            <p>La eliminación retira tus datos de cuenta. Los documentos e información sujetos a conservación legal pueden mantenerse.</p>
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')
                <x-form-field name="password" label="Confirma con tu contraseña" type="password" required autocomplete="current-password" />
                <x-button type="submit">Eliminar mi cuenta</x-button>
            </form>
        </div>
    </section>
</x-app>
