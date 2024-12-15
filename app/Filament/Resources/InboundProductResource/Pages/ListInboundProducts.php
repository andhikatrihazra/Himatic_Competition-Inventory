<?php

namespace App\Filament\Resources\InboundProductResource\Pages;

use App\Filament\Resources\InboundProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInboundProducts extends ListRecords
{
    protected static string $resource = InboundProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
