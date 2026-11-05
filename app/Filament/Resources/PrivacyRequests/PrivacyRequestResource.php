<?php

namespace App\Filament\Resources\PrivacyRequests;

use App\Filament\Resources\PrivacyRequests\Pages\ManagePrivacyRequests;
use App\Models\PrivacyRequest;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PrivacyRequestResource extends Resource
{
    protected static ?string $model = PrivacyRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Solicitudes de privacidad';

    protected static ?string $modelLabel = 'solicitud de privacidad';

    protected static ?string $pluralModelLabel = 'solicitudes de privacidad';

    protected static string|\UnitEnum|null $navigationGroup = 'Clientes';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')->label('Estado')->options([
                    'pending' => 'Pendiente',
                    'in_review' => 'En revisión',
                    'completed' => 'Resuelta',
                    'declined' => 'No procede',
                ])->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')->label('Cuenta')->placeholder('Eliminada')->searchable(),
                TextColumn::make('type')->label('Solicitud')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'access' => 'Acceso',
                        'rectification' => 'Rectificación',
                        'erasure' => 'Supresión',
                        default => $state,
                    }),
                TextColumn::make('status')->label('Estado')->badge(),
                TextColumn::make('created_at')->label('Recibida')->dateTime('d/m/Y')->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePrivacyRequests::route('/'),
        ];
    }
}
