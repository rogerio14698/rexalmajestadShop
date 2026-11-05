<?php

namespace App\Filament\Resources\PortfolioSections;

use App\Filament\Resources\PortfolioSections\Pages\ManagePortfolioSections;
use App\Models\PortfolioSection;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PortfolioSectionResource extends Resource
{
    protected static ?string $model = PortfolioSection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $navigationLabel = 'Portfolio (historia)';

    protected static ?string $modelLabel = 'sección del portfolio';

    protected static ?string $pluralModelLabel = 'secciones del portfolio';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('heading')->label('Título de la sección')->required()->maxLength(160),
                TextInput::make('position')->label('Orden')->numeric()->minValue(0)->default(0),
                Textarea::make('body')->label('Texto')->rows(8)->columnSpanFull(),
                Select::make('artwork_id')
                    ->label('Obra destacada (opcional)')
                    ->relationship('artwork', 'title')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) $record->title)
                    ->searchable()
                    ->preload()
                    ->nullable(),
                DateTimePicker::make('published_at')->label('Publicada'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')->label('Orden')->sortable(),
                TextColumn::make('heading')->label('Título')->searchable(),
                TextColumn::make('artwork.title')->label('Obra destacada'),
                TextColumn::make('published_at')->label('Publicación')->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('position')
            ->recordActions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Eliminar'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePortfolioSections::route('/'),
        ];
    }
}
