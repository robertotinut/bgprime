<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('telegram_username')
                    ->label('Telegram')
                    ->formatStateUsing(fn ($state) => $state ? "@{$state}" : '-')
                    ->searchable(),

                TextColumn::make('telegram_id')
                    ->label('Telegram ID')
                    ->searchable(),

                TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('Total Orders')
                    ->badge()
                    ->color('primary'),

                IconColumn::make('delivery_bot_started_at')
                    ->label('Delivery Bot Active')
                    ->boolean()
                    ->getStateUsing(fn (User $record) => $record->delivery_bot_started_at !== null),

                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->label('Registered At')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
