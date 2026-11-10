<?php

namespace App\Filament\Resources\Artworks\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $title = 'Fotografías de la obra';

    protected static ?string $modelLabel = 'fotografía';

    protected static ?string $pluralModelLabel = 'fotografías';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file_name')
            ->columns([
                ImageColumn::make('original_url')
                    ->label('Vista previa')
                    ->square()
                    ->size(80),
                TextColumn::make('file_name')
                    ->label('Nombre del archivo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('custom_properties.alt')
                    ->label('Texto alternativo (alt)')
                    ->default('—')
                    ->limit(40),
                TextColumn::make('size')
                    ->label('Tamaño')
                    ->formatStateUsing(fn ($state) => number_format((int) $state / 1024, 0, ',', '.').' KB'),
                TextColumn::make('created_at')
                    ->label('Fecha de subida')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Añadir fotografía')
                    ->modalHeading('Subir fotografía a la obra')
                    ->form([
                        FileUpload::make('uploaded_file')
                            ->label('Archivo de imagen')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('artwork-temp')
                            ->required(),
                        TextInput::make('alt')
                            ->label('Texto alternativo (alt)')
                            ->placeholder('Descripción accesible de la imagen')
                            ->maxLength(255),
                    ])
                    ->using(function (array $data, RelationManager $livewire): Media {
                        $owner = $livewire->getOwnerRecord();
                        $filePath = $data['uploaded_file'];
                        $fullPath = Storage::disk('public')->path($filePath);

                        $media = $owner->addMedia($fullPath)
                            ->preservingOriginal()
                            ->withCustomProperties(['alt' => $data['alt'] ?? ''])
                            ->toMediaCollection('artwork');

                        if (Storage::disk('public')->exists($filePath)) {
                            Storage::disk('public')->delete($filePath);
                        }

                        return $media;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->form([
                        TextInput::make('file_name')
                            ->label('Nombre del archivo')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('custom_properties.alt')
                            ->label('Texto alternativo (alt)')
                            ->maxLength(255),
                    ]),
                DeleteAction::make()
                    ->label('Eliminar'),
            ]);
    }
}
