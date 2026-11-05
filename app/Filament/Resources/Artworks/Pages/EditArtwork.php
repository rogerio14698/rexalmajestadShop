<?php

namespace App\Filament\Resources\Artworks\Pages;

use App\Filament\Resources\Artworks\ArtworkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditArtwork extends EditRecord
{
    protected static string $resource = ArtworkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $uploaded = $this->data['uploaded_images'] ?? [];

        if (is_array($uploaded) && count($uploaded) > 0) {
            foreach ($uploaded as $filePath) {
                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    $fullPath = Storage::disk('public')->path($filePath);
                    $this->record->addMedia($fullPath)
                        ->preservingOriginal()
                        ->toMediaCollection('artwork');
                }
            }
        }
    }
}
