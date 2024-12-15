<?php

namespace App\Filament\Resources\OutboundProductResource\Pages;

use App\Filament\Resources\OutboundProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOutboundProduct extends EditRecord
{
    protected static string $resource = OutboundProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
