<?php

namespace App\Filament\Resources\ShippingRates;

use App\Filament\Resources\ShippingRates\Pages\ManageShippingRates;
use App\Models\ShippingRate;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingRateResource extends Resource
{
    protected static ?string $model = ShippingRate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Tarifas de envío';

    protected static ?string $modelLabel = 'tarifa de envío';

    protected static ?string $pluralModelLabel = 'tarifas de envío';

    protected static string|\UnitEnum|null $navigationGroup = 'Tienda';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('shipping_zone_id')->label('Zona')->relationship('zone', 'name')->required(),
                TextInput::make('min_weight_grams')->label('Peso mínimo (g)')->numeric()->minValue(0)->required(),
                TextInput::make('max_weight_grams')->label('Peso máximo (g)')->numeric()->minValue(1)->required()
                    ->gt('min_weight_grams'),
                TextInput::make('price_cents')->label('Precio (céntimos)')->numeric()->minValue(0)->required(),
                TextInput::make('currency')->label('Moneda ISO')->required()->length(3)
                    ->default(config('commerce.currency', 'EUR'))
                    ->disabled()
                    ->dehydrated(),
                Toggle::make('is_active')->label('Activa')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('zone.name')->label('Zona')->sortable(),
                TextColumn::make('min_weight_grams')->label('Desde (g)'),
                TextColumn::make('max_weight_grams')->label('Hasta (g)'),
                TextColumn::make('price_cents')->label('Precio')
                    ->formatStateUsing(fn ($state, ShippingRate $record) => number_format((int) $state / 100, 2, ',', '.').' '.$record->currency),
                IconColumn::make('is_active')->label('Activa')->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageShippingRates::route('/'),
        ];
    }
}
