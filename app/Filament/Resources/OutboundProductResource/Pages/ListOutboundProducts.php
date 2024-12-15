<?php

namespace App\Filament\Resources\OutboundProductResource\Pages;

use App\Filament\Resources\OutboundProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOutboundProducts extends ListRecords
{
    protected static string $resource = OutboundProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
