<?php

namespace App\Filament\Resources\Artworks\Pages;

use App\Filament\Resources\Artworks\ArtworkResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateArtwork extends CreateRecord
{
    protected static string $resource = ArtworkResource::class;

    protected function afterCreate(): void
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
