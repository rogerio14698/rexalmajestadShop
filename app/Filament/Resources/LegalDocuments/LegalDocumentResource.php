<?php

namespace App\Filament\Resources\LegalDocuments;

use App\Filament\Resources\LegalDocuments\Pages\ManageLegalDocuments;
use App\Models\LegalDocument;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LegalDocumentResource extends Resource
{
    protected static ?string $model = LegalDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Documentos legales';

    protected static ?string $modelLabel = 'documento legal';

    protected static ?string $pluralModelLabel = 'documentos legales';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('slug')->label('Tipo de documento')->options([
                    'aviso-legal' => 'Aviso legal',
                    'privacidad' => 'Privacidad',
                    'cookies' => 'Cookies',
                    'compra-y-devoluciones' => 'Compra y devoluciones',
                    'envios' => 'Envíos',
                ])->required()->unique(ignoreRecord: true),
                TextInput::make('title')->label('Título')->required()->maxLength(255),
                Textarea::make('body')->label('Texto revisado')->rows(20)->required()->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Título')->searchable(),
                TextColumn::make('slug')->label('Tipo'),
                TextColumn::make('published_at')->label('Publicación')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLegalDocuments::route('/'),
        ];
    }
}
