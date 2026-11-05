<?php

namespace App\Filament\Resources\Exhibitions;

use App\Filament\Resources\Exhibitions\Pages\ManageExhibitions;
use App\Models\Exhibition;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExhibitionResource extends Resource
{
    protected static ?string $model = Exhibition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Exposiciones';

    protected static ?string $modelLabel = 'exposición';

    protected static ?string $pluralModelLabel = 'exposiciones';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->label('Título')->required()->maxLength(255),
                TextInput::make('slug')->label('URL')->required()->unique(ignoreRecord: true)->maxLength(255),
                TextInput::make('venue')->label('Lugar')->maxLength(255),
                TextInput::make('city')->label('Ciudad')->maxLength(120),
                TextInput::make('country_code')->label('País ISO')->length(2)->alpha(),
                DatePicker::make('starts_at')->label('Fecha de inicio'),
                DatePicker::make('ends_at')->label('Fecha de fin')->afterOrEqual('starts_at'),
                Textarea::make('description')->label('Descripción (español)')->rows(8)->columnSpanFull(),
                DateTimePicker::make('published_at')
                    ->label('Publicada')
                    ->nullable()
                    ->helperText('Puedes dejarlo en blanco; si no se indica fecha, se asignará automáticamente la fecha y hora actual al guardar.')
                    ->default(now()),
                \Filament\Forms\Components\FileUpload::make('uploaded_images')
                    ->label('Fotografías de la exposición')
                    ->disk('public')
                    ->directory('exhibitions-temp')
                    ->multiple()
                    ->reorderable()
                    ->image()
                    ->imageEditor()
                    ->helperText('Puedes subir una o varias imágenes. Si subes más de una, se mostrarán como carrusel.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Título')->searchable()->sortable(),
                TextColumn::make('venue')->label('Lugar'),
                TextColumn::make('city')->label('Ciudad'),
                TextColumn::make('starts_at')->label('Inicio')->date()->sortable(),
                TextColumn::make('published_at')->label('Publicación')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        if (empty($data['published_at'])) {
                            $data['published_at'] = now();
                        }

                        return $data;
                    })
                    ->after(function ($record, array $data): void {
                        $uploaded = $data['uploaded_images'] ?? [];

                        if (is_array($uploaded) && count($uploaded) > 0) {
                            foreach ($uploaded as $filePath) {
                                if ($filePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($filePath)) {
                                    $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($filePath);
                                    $record->addMedia($fullPath)
                                        ->preservingOriginal()
                                        ->toMediaCollection('exhibition');
                                }
                            }
                        }
                    }),
                \Filament\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageExhibitions::route('/'),
        ];
    }
}
