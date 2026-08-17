<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Full Name'),

                TextInput::make('telegram_username')
                    ->label('Telegram Username')
                    ->prefix('@'),

                TextInput::make('telegram_id')
                    ->label('Telegram ID')
                    ->numeric(),

                TextInput::make('email')
                    ->email(),

                DateTimePicker::make('transaction_bot_started_at')
                    ->label('Transaction Bot Active At')
                    ->disabled(),

                DateTimePicker::make('delivery_bot_started_at')
                    ->label('Delivery Bot Active At')
                    ->disabled(),
            ]);
    }
}
