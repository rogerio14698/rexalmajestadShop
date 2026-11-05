<label for="type-{{ $address?->id ?? 'new' }}">Tipo</label>
<select id="type-{{ $address?->id ?? 'new' }}" name="type" required>
    <option value="shipping" @selected(old('type', $address?->type) === 'shipping')>Envío</option>
    <option value="billing" @selected(old('type', $address?->type) === 'billing')>Facturación</option>
</select>
@foreach ([
    'recipient' => 'Nombre',
    'line_1' => 'Dirección',
    'line_2' => 'Complemento',
    'city' => 'Ciudad',
    'region_code' => 'Región o provincia (código)',
    'postal_code' => 'Código postal',
    'country_code' => 'País (código ISO 2 letras)',
    'tax_id' => 'NIF / CIF',
] as $name => $label)
    <x-form-field :name="$name" :id="$name.'-'.($address?->id ?? 'new')" :label="$label" :required="! in_array($name, ['line_2', 'tax_id'])" :value="$address?->{$name}" />
@endforeach
<label><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $address?->is_default))> Dirección predeterminada</label>
