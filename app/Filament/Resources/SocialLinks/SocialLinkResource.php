<?php

namespace App\Filament\Resources\SocialLinks;

use App\Filament\Resources\SocialLinks\Pages\ManageSocialLinks;
use App\Models\SocialLink;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SocialLinkResource extends Resource
{
    protected static ?string $model = SocialLink::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static ?string $navigationLabel = 'Redes Sociales';

    protected static ?string $modelLabel = 'red social';

    protected static ?string $pluralModelLabel = 'redes sociales';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre de la red')
                    ->placeholder('Instagram, TikTok, Twitter/X, Behance...')
                    ->required()
                    ->maxLength(100),
                TextInput::make('url')
                    ->label('Enlace (URL)')
                    ->placeholder('https://instagram.com/rexalmajestad')
                    ->url()
                    ->required()
                    ->maxLength(255),
                Select::make('platform')
                    ->label('Plataforma')
                    ->options([
                        'instagram' => 'Instagram',
                        'tiktok' => 'TikTok',
                        'twitter' => 'Twitter / X',
                        'facebook' => 'Facebook',
                        'youtube' => 'YouTube',
                        'pinterest' => 'Pinterest',
                        'linkedin' => 'LinkedIn',
                        'behance' => 'Behance',
                        'otro' => 'Otro',
                    ])
                    ->default('instagram'),
                TextInput::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->default(0)
                    ->helperText('Menor número se muestra primero'),
                Toggle::make('is_active')
                    ->label('Visible en la web')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('Orden')->sortable(),
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('url')->label('Enlace')->limit(40),
                TextColumn::make('platform')->label('Plataforma')->badge(),
                IconColumn::make('is_active')->label('Visible')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Eliminar'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSocialLinks::route('/'),
        ];
    }
}
