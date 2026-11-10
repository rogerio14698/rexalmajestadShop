<?php

namespace App\Filament\Resources\PortfolioSections\Pages;

use App\Filament\Resources\PortfolioSections\PortfolioSectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Storage;

class ManagePortfolioSections extends ManageRecords
{
    protected static string $resource = PortfolioSectionResource::class;

    public function deleteMedia(int $mediaId): void
    {
        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
        if ($media && $media->model_type === \App\Models\PortfolioSection::class) {
            $media->delete();
            \Filament\Notifications\Notification::make()
                ->title('Fotografía eliminada')
                ->success()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
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
        ];
    }
}

