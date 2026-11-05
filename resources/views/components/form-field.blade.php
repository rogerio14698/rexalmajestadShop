@props([
    'name',
    'label',
    'type' => 'text',
    'required' => false,
    'help' => null,
    'value' => null,
    'id' => null,
])

@php($fieldId = $id ?? $name)
<div class="form-field">
    <label class="form-field__label" for="{{ $fieldId }}">{{ $label }}</label>
    @if ($type === 'textarea')
        <textarea id="{{ $fieldId }}" name="{{ $name }}" {{ $required ? 'required' : '' }} {{ $attributes->class(['form-field__control']) }}>{{ old($name, $value) }}</textarea>
    @else
        <input id="{{ $fieldId }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" {{ $required ? 'required' : '' }} {{ $attributes->class(['form-field__control']) }}>
    @endif
    @if ($help)
        <span class="form-field__help" id="{{ $name }}-help">{{ $help }}</span>
    @endif
    @error($name)
        <span class="form-field__error" id="{{ $fieldId }}-error">{{ $message }}</span>
    @enderror
</div>
