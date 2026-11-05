<?php

namespace App\Filament\Resources\TaxRates;

use App\Filament\Resources\TaxRates\Pages\ManageTaxRates;
use App\Models\TaxRate;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
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

class TaxRateResource extends Resource
{
    protected static ?string $model = TaxRate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Tipos de IVA';

    protected static ?string $modelLabel = 'tipo de IVA';

    protected static ?string $pluralModelLabel = 'tipos de IVA';

    protected static string|\UnitEnum|null $navigationGroup = 'Fiscal';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('country_code')->label('País ISO')->required()->length(2)->alpha(),
                TextInput::make('region_code')->label('Región (opcional)')->maxLength(32),
                Select::make('tax_class')->label('Clase fiscal')
                    ->options(['original_art' => 'Obra original', 'shipping' => 'Envío'])
                    ->required(),
                TextInput::make('rate_basis_points')->label('Tipo en puntos base')->numeric()->minValue(0)->maxValue(10000)->required(),
                DatePicker::make('valid_from')->label('Válido desde')->required(),
                DatePicker::make('valid_until')->label('Válido hasta'),
                Toggle::make('is_active')->label('Activo')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('country_code')->label('País')->sortable(),
                TextColumn::make('region_code')->label('Región')->placeholder('General'),
                TextColumn::make('tax_class')->label('Clase'),
                TextColumn::make('rate_basis_points')->label('Tipo')
                    ->formatStateUsing(fn ($state) => number_format((int) $state / 100, 2, ',', '.').'%'),
                TextColumn::make('valid_from')->label('Desde')->date()->sortable(),
                IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTaxRates::route('/'),
        ];
    }
}
