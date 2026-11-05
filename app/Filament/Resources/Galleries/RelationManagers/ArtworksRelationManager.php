<?php

namespace App\Filament\Resources\Galleries\RelationManagers;

use Filament\Actions\DissociateAction;
use Filament\Actions\AssociateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ArtworksRelationManager extends RelationManager
{
    protected static string $relationship = 'artworks';

    protected static ?string $title = 'Obras de la colección';

    protected static ?string $modelLabel = 'obra';

    protected static ?string $pluralModelLabel = 'obras';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->label('Título')->searchable(),
                TextColumn::make('category.name')->label('Categoría')->badge(),
                TextColumn::make('year')->label('Año')->sortable(),
            ])
            ->headerActions([
                AssociateAction::make()
                    ->label('Asociar obra')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['title', 'slug']),
            ])
            ->recordActions([
                DissociateAction::make()->label('Quitar de la colección'),
            ]);
    }
}
