<?php

namespace App\Filament\Resources\Galleries;

use App\Filament\Resources\Galleries\Pages\CreateGallery;
use App\Filament\Resources\Galleries\Pages\EditGallery;
use App\Filament\Resources\Galleries\Pages\ListGalleries;
use App\Filament\Resources\Galleries\RelationManagers\ArtworksRelationManager;
use App\Models\Gallery;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Colecciones';

    protected static ?string $modelLabel = 'colección';

    protected static ?string $pluralModelLabel = 'colecciones';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nombre')->required()->maxLength(120)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, callable $set, ?\App\Models\Gallery $record, ?string $operation) {
                        if ($operation === 'create' || ! $record?->slug) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')->label('URL')->required()->unique(ignoreRecord: true)->maxLength(120),
                Textarea::make('description')->label('Descripción')->rows(4)->columnSpanFull(),
                DateTimePicker::make('published_at')->label('Publicada'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('slug')->label('URL'),
                TextColumn::make('artworks_count')->label('Obras')->counts('artworks'),
                TextColumn::make('published_at')->label('Publicación')->dateTime('d/m/Y')->sortable(),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Eliminar'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ArtworksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGalleries::route('/'),
            'create' => CreateGallery::route('/create'),
            'edit' => EditGallery::route('/{record}/edit'),
        ];
    }
}
