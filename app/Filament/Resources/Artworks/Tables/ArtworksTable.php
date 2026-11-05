<?php

namespace App\Filament\Resources\Artworks\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ArtworksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Título')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Categoría')->badge(),
                TextColumn::make('gallery.name')->label('Colección')->toggleable(),
                TextColumn::make('year')->label('Año')->sortable(),
                TextColumn::make('price_cents')->label('Precio')
                    ->formatStateUsing(fn ($state) => number_format((int) $state / 100, 2, ',', '.').' '.config('commerce.currency', 'EUR')),
                TextColumn::make('stock')->label('Stock')->sortable(),
                IconColumn::make('is_for_sale')->label('A la venta')->boolean(),
                TextColumn::make('published_at')->label('Publicación')->dateTime('d/m/Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->label('Categoría')->relationship('category', 'name'),
                SelectFilter::make('gallery')->label('Colección')->relationship('gallery', 'name'),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
                Action::make('addImage')
                    ->label('Añadir imagen')
                    ->form([FileUpload::make('file')->label('Fotografía')->image()->disk('public')->directory('artwork')->required()])
                    ->action(fn (\App\Models\Artwork $record, array $data) => $record->addMediaFromDisk($data['file'], 'public')->toMediaCollection('artwork')),
            ]);
    }
}
