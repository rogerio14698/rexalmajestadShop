<x-app title="Checkout | Rexalmajestad" description="Completa los datos de envío y pago.">
    <section class="content-page checkout-page" aria-labelledby="checkout-title">
        <h1 id="checkout-title">Finalizar pedido</h1>
        @if ($errors->any())
            <div class="form-errors" role="alert" aria-labelledby="checkout-errors-title">
                <h2 id="checkout-errors-title">Revisa estos datos</h2>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('checkout.store') }}" class="checkout-form">
            @csrf
            <x-form-field name="email" label="Correo electrónico" type="email" required autocomplete="email" />
            <h2>Dirección de envío</h2>
            @foreach ([
                'shipping_recipient' => ['Nombre de quien recibe', 'text'],
                'shipping_line_1' => ['Dirección', 'text'],
                'shipping_line_2' => ['Complemento', 'text'],
                'shipping_city' => ['Ciudad', 'text'],
                'shipping_region' => ['Región o provincia (código)', 'text'],
                'shipping_postal_code' => ['Código postal', 'text'],
                'shipping_country' => ['País (código ISO 2 letras)', 'text'],
                'shipping_tax_id' => ['NIF / CIF (opcional)', 'text'],
            ] as $name => [$label, $type])
                <x-form-field :name="$name" :label="$label" :type="$type" :required="! in_array($name, ['shipping_line_2', 'shipping_tax_id'])" />
            @endforeach
            <label class="checkout-form__checkbox">
                <input type="checkbox" name="billing_same" value="1" checked>
                La dirección de facturación es la misma
            </label>
            <details>
                <summary>Usar otra dirección de facturación</summary>
                @foreach ([
                    'billing_recipient' => ['Nombre de quien recibe', 'text'],
                    'billing_line_1' => ['Dirección', 'text'],
                    'billing_line_2' => ['Complemento', 'text'],
                    'billing_city' => ['Ciudad', 'text'],
                    'billing_region' => ['Región o provincia (código)', 'text'],
                    'billing_postal_code' => ['Código postal', 'text'],
                    'billing_country' => ['País (código ISO 2 letras)', 'text'],
                    'billing_tax_id' => ['NIF / CIF (opcional)', 'text'],
                ] as $name => [$label, $type])
                    <x-form-field :name="$name" :label="$label" :type="$type" :required="! in_array($name, ['billing_line_2', 'billing_tax_id'])" />
                @endforeach
            </details>
            <x-form-field name="website" label="Sitio web" tabindex="-1" autocomplete="off" class="checkout-form__honeypot" />
            <fieldset>
                <legend>Forma de pago</legend>
                <label><input type="radio" name="payment_provider" value="stripe" checked> Tarjeta (Stripe)</label>
                <label><input type="radio" name="payment_provider" value="paypal"> PayPal</label>
            </fieldset>
            <p>El importe final se calcula en el servidor según el destino y las tarifas configuradas.</p>
            <x-button type="submit">Continuar con el pago seguro</x-button>
        </form>
    </section>
</x-app>
