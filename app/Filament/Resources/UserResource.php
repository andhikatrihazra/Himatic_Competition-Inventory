<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use App\Filament\Resources\UserResource\Pages;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Users Management';

    protected static ?string $label = 'User';

    protected static ?string $pluralLabel = 'Users';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
        ->schema([
            Fieldset::make('User Details') // Membuat grup untuk form
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
        
                    TextInput::make('email')
                        ->required()
                        ->email()
                        ->maxLength(255),
        
                        TextInput::make('password')
                        ->required(fn ($record) => $record ? !$record->exists : true) // Ensure $record is not null
                        ->password()
                        ->minLength(8)
                        ->label('Password')
                        ->dehydrated(fn ($state) => filled($state))
                        ->dehydrateStateUsing(fn ($state) => $state ? bcrypt($state) : null),
                    
        
                    Select::make('roles')
                        ->relationship('roles', 'name') // Menarik data dari relationship
                        ->required(),
                ])
                ->columns(1), // Atur fieldset menjadi 1 kolom
        ]);
        
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Name'),

                TextColumn::make('email')
                    ->sortable()
                    ->searchable()
                    ->label('Email'),

                    BadgeColumn::make('roles.name')
                    ->color(fn ($state) => match ($state) {
                        'admin' => 'danger', // Red for admin
                        'user' => 'primary', // Blue for user admin
                        default => 'gray', // Default color for other roles
                    })
                
                
            ])
            ->filters([
                // Custom filters can be added here
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}