<?php

namespace App\Filament\Resources\ShippingInquiries;

use App\Filament\Resources\ShippingInquiries\Pages\ManageShippingInquiries;
use App\Models\ShippingInquiry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingInquiryResource extends Resource
{
    protected static ?string $model = ShippingInquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Consultas de envío';

    protected static ?string $modelLabel = 'consulta de envío';

    protected static ?string $pluralModelLabel = 'consultas de envío';

    protected static string|\UnitEnum|null $navigationGroup = 'Clientes';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Placeholder::make('email')->label('Correo')->content(fn (?ShippingInquiry $record) => $record?->email ?? ''),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('artwork.title')->label('Obra')->searchable(),
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('email')->label('Correo')->searchable(),
                TextColumn::make('country_code')->label('País'),
                TextColumn::make('created_at')->label('Recibida')->dateTime('d/m/Y')->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageShippingInquiries::route('/'),
        ];
    }
}
