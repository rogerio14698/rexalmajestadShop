<x-filament-widgets::widget>
    <x-filament::section heading="Exportaciones">
        <div class="flex flex-wrap gap-3">
            <x-filament::button tag="a" href="{{ route('admin.exports.orders') }}">Descargar pedidos CSV</x-filament::button>
            <x-filament::button tag="a" href="{{ route('admin.exports.artworks') }}">Descargar obras CSV</x-filament::button>
            <x-filament::button tag="a" href="{{ route('admin.exports.vat', ['from' => now()->startOfYear()->toDateString(), 'to' => now()->toDateString()]) }}">Descargar informe de IVA</x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
