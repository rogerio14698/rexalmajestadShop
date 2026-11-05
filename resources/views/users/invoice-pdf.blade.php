<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->type === 'credit_note' ? 'Nota rectificativa' : 'Factura' }} {{ $invoice->number }}</title>
    <style>
        body { color: #172044; font: 12px DejaVu Sans, sans-serif; line-height: 1.5; }
        h1, h2 { color: #244bd3; }
        table { border-collapse: collapse; margin: 24px 0; width: 100%; }
        th, td { border-bottom: 1px solid #d8dced; padding: 8px; text-align: left; }
        .amount { text-align: right; }
        .notice { border: 1px solid #d8dced; padding: 12px; }
    </style>
</head>
<body>
    @php($amountSign = $invoice->type === 'credit_note' ? '-' : '')
    <h1>{{ $invoice->type === 'credit_note' ? 'Nota rectificativa' : 'Factura' }}</h1>
    <p><strong>Número:</strong> {{ $invoice->number }}<br>
        <strong>Fecha:</strong> {{ $invoice->issued_at->format('d/m/Y') }}</p>
    <h2>Emisor</h2>
    <p>{{ $invoice->issuer_snapshot['legal_name'] }}<br>
        {{ $invoice->issuer_snapshot['tax_id'] }}<br>
        {{ $invoice->issuer_snapshot['address'] }}, {{ $invoice->issuer_snapshot['postal_code'] }} {{ $invoice->issuer_snapshot['city'] }}<br>
        {{ $invoice->issuer_snapshot['country_code'] }}</p>
    <h2>Cliente</h2>
    <p>{{ $invoice->customer_name }}<br>
        {{ $invoice->customer_email }}<br>
        {{ $invoice->customer_tax_id }}<br>
        {{ $invoice->billing_address_snapshot['line_1'] }}, {{ $invoice->billing_address_snapshot['postal_code'] }} {{ $invoice->billing_address_snapshot['city'] }}<br>
        {{ $invoice->billing_address_snapshot['country_code'] }}</p>
    @if ($invoice->type === 'credit_note')
        <p class="notice">Rectifica {{ $invoice->corrects->number }}. Motivo: {{ $invoice->correction_reason }}</p>
    @endif
    <table>
        <thead>
            <tr><th>Concepto</th><th>Cantidad</th><th>Base</th><th>IVA</th><th>Total</th></tr>
        </thead>
        <tbody>
            @foreach ($invoice->items_snapshot as $item)
                <tr>
                    <td>{{ $item['title'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>{{ $amountSign }}{{ number_format(abs($item['net_cents']) / 100, 2, ',', '.') }} {{ $invoice->currency }}</td>
                    <td>{{ $amountSign }}{{ number_format(abs($item['tax_cents']) / 100, 2, ',', '.') }} ({{ number_format($item['tax_rate_basis_points'] / 100, 2, ',', '.') }}%)</td>
                    <td>{{ $amountSign }}{{ number_format(abs($item['total_cents']) / 100, 2, ',', '.') }} {{ $invoice->currency }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><th colspan="4" class="amount">Base imponible</th><td>{{ $amountSign }}{{ number_format($invoice->net_cents / 100, 2, ',', '.') }} {{ $invoice->currency }}</td></tr>
            <tr><th colspan="4" class="amount">IVA</th><td>{{ $amountSign }}{{ number_format($invoice->tax_cents / 100, 2, ',', '.') }} {{ $invoice->currency }}</td></tr>
            <tr><th colspan="4" class="amount">Total</th><td>{{ $amountSign }}{{ number_format($invoice->total_cents / 100, 2, ',', '.') }} {{ $invoice->currency }}</td></tr>
        </tfoot>
    </table>
</body>
</html>
