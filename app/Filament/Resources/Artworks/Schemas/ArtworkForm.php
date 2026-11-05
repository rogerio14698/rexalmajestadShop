<?php

namespace App\Filament\Resources\Artworks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArtworkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->label('Título')->required()->maxLength(255),
                TextInput::make('slug')->label('URL')->required()->unique(ignoreRecord: true)->maxLength(255),
                Select::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')->label('Nombre')->required()->maxLength(120)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')->label('URL')->required()->maxLength(120)->unique('categories', 'slug'),
                    ]),
                Select::make('gallery_id')
                    ->label('Colección (obras relacionadas)')
                    ->relationship('gallery', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->createOptionForm([
                        TextInput::make('name')->label('Nombre')->required()->maxLength(120)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')->label('URL')->required()->maxLength(120)->unique('galleries', 'slug'),
                    ]),
                TextInput::make('year')->label('Año')->numeric()->minValue(1800)->maxValue(now()->year + 1)->required(),
                TextInput::make('technique')->label('Técnica')->required()->maxLength(255),
                TextInput::make('height_cm')->label('Alto (cm)')->numeric()->minValue(0),
                TextInput::make('width_cm')->label('Ancho (cm)')->numeric()->minValue(0),
                TextInput::make('depth_cm')->label('Fondo (cm)')->numeric()->minValue(0),
                TextInput::make('weight_grams')->label('Peso (g)')->numeric()->minValue(0)->required(),
                TextInput::make('series')->label('Serie')->maxLength(255),
                Textarea::make('description')->label('Descripción (español)')->rows(6)->columnSpanFull(),
                TextInput::make('price_cents')->label('Precio (céntimos)')->numeric()->minValue(0),
                TextInput::make('stock')->label('Stock')->numeric()->minValue(0)->required(),
                Select::make('tax_class')
                    ->label('Clase fiscal')
                    ->options(['original_art' => 'Obra original'])
                    ->required(),
                Toggle::make('is_for_sale')->label('A la venta'),
                Toggle::make('is_sold')->label('Vendida'),
                Toggle::make('ask_shipping')->label('Consultar envío'),
                DateTimePicker::make('published_at')->label('Publicada'),
            ]);
    }
}
