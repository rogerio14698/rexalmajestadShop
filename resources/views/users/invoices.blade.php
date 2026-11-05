<x-app title="Mis facturas | Rexalmajestad">
    <section class="account-page" aria-labelledby="invoices-title">
        <header>
            <h1 id="invoices-title">Mis facturas</h1>
        </header>

        <x-account-navigation :breadcrumbs="['Mis facturas' => '']" />

        <ul class="account-orders-list">
            @forelse ($invoices as $invoice)
                <li class="account-order-card">
                    <div class="account-order-card__info">
                        <strong>{{ $invoice->type === 'credit_note' ? 'Nota rectificativa' : 'Factura' }} {{ $invoice->number }}</strong>
                        <span>{{ $invoice->created_at?->format('d/m/Y') }}</span>
                    </div>
                    <a class="button button--secondary" href="{{ route('invoices.download', $invoice) }}">Descargar PDF</a>
                </li>
            @empty
                <p>Todavía no hay facturas disponibles.</p>
            @endforelse
        </ul>

        {{ $invoices->links() }}
    </section>
</x-app>
