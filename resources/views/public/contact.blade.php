<x-app title="Contacto | Rexalmajestad" description="Escribe a Rexalmajestad para consultas sobre su obra y exposiciones." :canonical="route('contact.create')">
    <x-breadcrumbs :items="['Contacto' => '']" />

    <section class="content-page" aria-labelledby="contact-title">
        <h1 id="contact-title">Contacto</h1>
        <p>Para consultas sobre obras, exposiciones o envíos, escribe un mensaje.</p>
        @if (session('status'))<p role="status">{{ session('status') }}</p>@endif
        <form method="POST" action="{{ route('contact.store') }}" class="checkout-form">
            @csrf
            <x-form-field name="name" label="Nombre" required autocomplete="name" />
            <x-form-field name="email" label="Correo electrónico" type="email" required autocomplete="email" />
            <x-form-field name="subject" label="Asunto" />
            <x-form-field name="message" label="Mensaje" type="textarea" required />
            <x-form-field name="website" label="Sitio web" tabindex="-1" autocomplete="off" class="checkout-form__honeypot" />
            <x-button type="submit">Enviar mensaje</x-button>
        </form>
    </section>
</x-app>