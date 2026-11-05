<x-app title="Mis direcciones | Rexalmajestad">
    <section class="account-page" aria-labelledby="addresses-title">
        <header>
            <h1 id="addresses-title">Mis direcciones</h1>
        </header>

        <x-account-navigation :breadcrumbs="['Mis direcciones' => '']" />

        @if (session('status'))
            <p class="account-status" role="status">{{ session('status') }}</p>
        @endif

        @foreach ($addresses as $address)
            <div class="account-privacy-box">
                <form method="POST" action="{{ route('account.addresses.update', $address) }}">
                    @csrf
                    @method('PATCH')
                    @include('users.partials.address-fields', ['address' => $address])
                    <x-button type="submit">Guardar cambios</x-button>
                </form>
                <form method="POST" action="{{ route('account.addresses.destroy', $address) }}">
                    @csrf
                    @method('DELETE')
                    <x-button variant="quiet" type="submit">Eliminar dirección</x-button>
                </form>
            </div>
        @endforeach

        <div class="account-privacy-box">
            <h2>Añadir dirección</h2>
            <form method="POST" action="{{ route('account.addresses.store') }}">
                @csrf
                @include('users.partials.address-fields', ['address' => null])
                <x-button type="submit">Añadir dirección</x-button>
            </form>
        </div>
    </section>
</x-app>
