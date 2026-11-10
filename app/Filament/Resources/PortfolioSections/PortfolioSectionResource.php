<?php

namespace App\Filament\Resources\PortfolioSections;

use App\Filament\Resources\PortfolioSections\Pages\ManagePortfolioSections;
use App\Models\PortfolioSection;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class PortfolioSectionResource extends Resource
{
    protected static ?string $model = PortfolioSection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected static ?string $navigationLabel = 'Bio y Trayectoria';

    protected static ?string $modelLabel = 'sección de bio';

    protected static ?string $pluralModelLabel = 'secciones de bio';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Tipo de sección')
                    ->options([
                        'bio' => 'Biografía / Statement',
                        'solo_exhibition' => 'Exposición en solitario',
                        'group_exhibition' => 'Exposición colectiva',
                        'award' => 'Premios y Distinciones',
                        'press' => 'Revistas y Prensa',
                        'education' => 'Formación / Estudios',
                        'other' => 'Otra trayectoria',
                    ])
                    ->default('bio')
                    ->required(),
                TextInput::make('heading')->label('Título')->required()->maxLength(160)
                    ->placeholder('Ej: Statement artístico, Nombre de muestra, Premio...'),
                TextInput::make('subtitle')->label('Subtítulo / Sala / Publicación (opcional)')
                    ->maxLength(160)
                    ->placeholder('Ej: Galería La Imprenta, Revista Vogue Art...'),
                TextInput::make('year_label')->label('Año o período (opcional)')
                    ->maxLength(60)
                    ->placeholder('Ej: 2024, 2021-2023...'),
                TextInput::make('position')->label('Orden de aparición')->numeric()->minValue(0)->default(0)
                    ->helperText('Menor número aparece primero.'),
                DateTimePicker::make('published_at')->label('Fecha de publicación')->default(now()),
                Textarea::make('body')->label('Texto descriptivo')->rows(6)->columnSpanFull(),
                Select::make('artwork_id')
                    ->label('Obra asociada (enlace interno opcional)')
                    ->relationship('artwork', 'title')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) $record->title)
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Si se selecciona, creará un enlace dinámico directo a la ficha de esta obra.'),
                TextInput::make('external_url')
                    ->label('Enlace web externo / personalizado (opcional)')
                    ->url()
                    ->maxLength(255)
                    ->placeholder('https://ejemplo.com/articulo-prensa')
                    ->helperText('Opcional. Introduce una URL si quieres enlazar a una web externa o artículo.'),
                TextInput::make('link_label')
                    ->label('Texto del botón o enlace (opcional)')
                    ->maxLength(120)
                    ->placeholder('Ej: Ver obra en detalle, Leer artículo en prensa...'),
                FileUpload::make('uploaded_gallery')
                    ->label('Galería de fotografías')
                    ->disk('public')
                    ->directory('bio-gallery-temp')
                    ->multiple()
                    ->reorderable()
                    ->image()
                    ->imageEditor()
                    ->helperText('Sube imágenes para esta sección. Si subes más de una se mostrará como carrusel.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')->label('Orden')->sortable(),
                TextColumn::make('type')->label('Tipo')->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'bio' => 'Biografía / Statement',
                        'solo_exhibition' => 'Expo Solitaria',
                        'group_exhibition' => 'Expo Colectiva',
                        'award' => 'Premio',
                        'press' => 'Revista / Prensa',
                        'education' => 'Formación',
                        default => 'Otro',
                    }),
                TextColumn::make('heading')->label('Título')->searchable(),
                ImageColumn::make('gallery_preview')
                    ->label('Fotos')
                    ->state(fn (PortfolioSection $record) => $record->getMedia('gallery')->map(fn ($m) => $m->getUrl('webp') ?: $m->getUrl())->all())
                    ->circular()
                    ->stacked()
                    ->limit(4)
                    ->limitedRemainingText(),
                TextColumn::make('year_label')->label('Año')->sortable(),
                TextColumn::make('published_at')->label('Publicada')->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('position')
            ->recordActions([
                Action::make('manageImages')
                    ->label('Ver fotos')
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->modalHeading(fn (PortfolioSection $record) => 'Fotografías: '.$record->heading)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalContent(fn (PortfolioSection $record) => view('admin.portfolio-gallery-modal', ['record' => $record])),
                EditAction::make()->label('Editar')
                    ->after(function ($record, array $data): void {
                        $uploaded = $data['uploaded_gallery'] ?? [];
                        if (is_array($uploaded) && count($uploaded) > 0) {
                            foreach ($uploaded as $filePath) {
                                if ($filePath && Storage::disk('public')->exists($filePath)) {
                                    $record->addMedia(Storage::disk('public')->path($filePath))
                                        ->preservingOriginal()
                                        ->toMediaCollection('gallery');
                                }
                            }
                        }
                    }),
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

