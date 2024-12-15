<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InboundProductResource\Pages;
use App\Filament\Resources\InboundProductResource\RelationManagers;
use App\Models\InboundProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InboundProductResource extends Resource
{
    protected static ?string $model = InboundProduct::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-circle';
    protected static ?string $navigationGroup = 'In | Out Product';
    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInboundProducts::route('/'),
            'create' => Pages\CreateInboundProduct::route('/create'),
            'edit' => Pages\EditInboundProduct::route('/{record}/edit'),
        ];
    }
}
