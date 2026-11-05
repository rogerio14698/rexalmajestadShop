<x-app title="Contacto | Rexalmajestad" description="Escribe a Rexalmajestad para consultas sobre su obra y exposiciones." :canonical="route('contact.create')">
    <x-breadcrumbs :items="['Contacto' => '']" />

    <section class="content-page contact-page" aria-labelledby="contact-title">
        <div class="contact-card" data-reveal>
            <header class="contact-card__header">
                <h1 id="contact-title">Contacto</h1>
                <p class="contact-card__lede">Escribe directamente al estudio para consultar sobre obras disponibles, piezas por encargo, colaboraciones o exposiciones.</p>
            </header>

            @if (session('status'))
                <div class="contact-card__status" role="status">
                    <p>{{ session('status') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('contact.store') }}" class="contact-form">
                @csrf
                <div class="contact-form__row">
                    <x-form-field name="name" label="Nombre" required autocomplete="name" />
                    <x-form-field name="email" label="Correo electrónico" type="email" required autocomplete="email" />
                </div>
                <x-form-field name="subject" label="Asunto (opcional)" />
                <x-form-field name="message" label="Mensaje" type="textarea" required rows="5" />
                <x-form-field name="website" label="Sitio web" tabindex="-1" autocomplete="off" class="checkout-form__honeypot" />
                <div class="contact-form__actions">
                    <x-button type="submit">Enviar mensaje al estudio</x-button>
                </div>
            </form>
        </div>
    </section>
</x-app>