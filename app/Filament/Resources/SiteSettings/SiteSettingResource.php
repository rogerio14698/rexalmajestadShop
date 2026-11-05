<?php

namespace App\Filament\Resources\SiteSettings;

use App\Filament\Resources\SiteSettings\Pages\ManageSiteSettings;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Fondos y Banners';

    protected static ?string $modelLabel = 'fondo / banner';

    protected static ?string $pluralModelLabel = 'fondos y banners';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Identificador (clave)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Por ejemplo: hero_shop, hero_exhibitions')
                    ->maxLength(80),
                TextInput::make('label')
                    ->label('Nombre descriptivo')
                    ->required()
                    ->maxLength(120),
                Select::make('artwork_id')
                    ->label('Asociar imagen de una Obra existente (opcional)')
                    ->relationship('artwork', 'title')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) $record->title)
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Si eliges una obra, se usará su fotografía de fondo.'),
                SpatieMediaLibraryFileUpload::make('image')
                    ->label('O subir imagen personalizada directamente')
                    ->collection('image')
                    ->disk('public')
                    ->image()
                    ->imageEditor()
                    ->helperText('Sube una imagen para este banner si no deseas vincularla a una obra.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Sección / Banner')->searchable(),
                TextColumn::make('key')->label('Clave'),
                TextColumn::make('artwork.title')->label('Obra asociada')->placeholder('Ninguna'),
                SpatieMediaLibraryImageColumn::make('image')->collection('image')->label('Imagen subida'),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Eliminar'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSiteSettings::route('/'),
        ];
    }
}
