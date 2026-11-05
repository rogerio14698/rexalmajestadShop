<?php

namespace App\Filament\Resources\ShippingInquiries\Pages;

use App\Filament\Resources\ShippingInquiries\ShippingInquiryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageShippingInquiries extends ManageRecords
{
    protected static string $resource = ShippingInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
