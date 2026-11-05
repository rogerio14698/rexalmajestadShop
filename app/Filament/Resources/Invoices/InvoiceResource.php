<?php

namespace App\Filament\Resources\Invoices;

use App\Filament\Resources\Invoices\Pages\ManageInvoices;
use App\Models\Invoice;
use App\Services\InvoiceService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Facturas';

    protected static ?string $modelLabel = 'factura';

    protected static ?string $pluralModelLabel = 'facturas';

    protected static string|\UnitEnum|null $navigationGroup = 'Fiscal';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Placeholder::make('number')->label('Número')->content(fn (?Invoice $record) => $record?->number ?? ''),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('Número')->searchable()->sortable(),
                TextColumn::make('type')->label('Tipo')->badge()
                    ->formatStateUsing(fn ($state) => $state === 'credit_note' ? 'Rectificativa' : 'Factura'),
                TextColumn::make('customer_name')->label('Cliente')->searchable(),
                TextColumn::make('total_cents')->label('Total')
                    ->formatStateUsing(fn ($state, Invoice $record) => number_format((int) $state / 100, 2, ',', '.').' '.$record->currency),
                TextColumn::make('issued_at')->label('Emisión')->dateTime('d/m/Y')->sortable(),
            ])
            ->recordActions([
                Action::make('pdf')
                    ->label('Descargar PDF')
                    ->url(fn (Invoice $record) => route('invoices.download', $record))
                    ->openUrlInNewTab(),
                Action::make('correction')
                    ->label('Emitir rectificativa')
                    ->visible(fn (Invoice $record) => $record->type === 'invoice')
                    ->form([Textarea::make('reason')->label('Motivo')->required()->maxLength(255)])
                    ->action(fn (Invoice $record, array $data) => app(InvoiceService::class)->createCorrection($record, $data['reason'])),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInvoices::route('/'),
        ];
    }
}
