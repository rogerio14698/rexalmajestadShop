<?php

namespace App\Filament\Resources\Exhibitions\Pages;

use App\Filament\Resources\Exhibitions\ExhibitionResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Storage;

class ManageExhibitions extends ManageRecords
{
    protected static string $resource = ExhibitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data): array {
                    if (empty($data['published_at'])) {
                        $data['published_at'] = now();
                    }

                    return $data;
                })
                ->after(function ($record, array $data): void {
                    $this->syncUploadedImages($record, $data);
                }),
        ];
    }

    public function configureTable(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return parent::configureTable($table)
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, $record): array {
                        return $data;
                    })
                    ->mutateDataUsing(function (array $data): array {
                        if (empty($data['published_at'])) {
                            $data['published_at'] = now();
                        }

                        return $data;
                    })
                    ->after(function ($record, array $data): void {
                        $this->syncUploadedImages($record, $data);
                    }),
                DeleteAction::make(),
            ]);
    }

    protected function syncUploadedImages($record, array $data): void
    {
        $uploaded = $data['uploaded_images'] ?? [];

        if (is_array($uploaded) && count($uploaded) > 0) {
            foreach ($uploaded as $filePath) {
                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    $fullPath = Storage::disk('public')->path($filePath);
                    $record->addMedia($fullPath)
                        ->preservingOriginal()
                        ->toMediaCollection('exhibition');
                }
            }
        }
    }
}

