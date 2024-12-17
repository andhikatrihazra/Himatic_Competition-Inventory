<?php

namespace App\Filament\Resources\OutboundProductResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords\Tab;
use App\Filament\Resources\OutboundProductResource;
use App\Filament\Resources\OutboundProductResource\Widgets\OutboundProductWidget;

class ListOutboundProducts extends ListRecords
{
    protected static string $resource = OutboundProductResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            OutboundProductWidget::class, 
        ];
    }
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
