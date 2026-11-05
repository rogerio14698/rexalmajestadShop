<?php

namespace App\Filament\Resources\ShippingZones;

use App\Filament\Resources\ShippingZones\Pages\ManageShippingZones;
use App\Models\ShippingZone;
use BackedEnum;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingZoneResource extends Resource
{
    protected static ?string $model = ShippingZone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Zonas de envío';

    protected static ?string $modelLabel = 'zona de envío';

    protected static ?string $pluralModelLabel = 'zonas de envío';

    protected static string|\UnitEnum|null $navigationGroup = 'Tienda';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                TagsInput::make('country_codes')->label('Países ISO (códigos de 2 letras)')->required(),
                TagsInput::make('region_codes')->label('Regiones (opcional; vacío = todo el país)'),
                Toggle::make('is_active')->label('Activa')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Zona')->searchable()->sortable(),
                TextColumn::make('country_codes')->label('Países')->badge(),
                TextColumn::make('region_codes')->label('Regiones')->badge()->placeholder('Todas'),
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
            'index' => ManageShippingZones::route('/'),
        ];
    }
}
