<?php

namespace App\Filament\Resources\InboundProductResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use App\Filament\Resources\InboundProductResource;
use Illuminate\Database\Eloquent\Builder;

class ListInboundProducts extends ListRecords
{
    protected static string $resource = InboundProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'Today' => Tab::make('Today')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereDate('created_at', now()->toDateString())
                ),
            'Month' => Tab::make('Month')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                ),
        ];
    }
}
