<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('invoice_number')
                    ->disabled(),

                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled(),

                TextInput::make('product_name')
                    ->disabled(),

                TextInput::make('amount')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled(),

                Select::make('payment_status')
                    ->options([
                        Order::PAYMENT_PENDING => 'Pending',
                        Order::PAYMENT_WAITING_CONFIRMATION => 'Waiting Confirmation',
                        Order::PAYMENT_PAID => 'Paid',
                        Order::PAYMENT_REJECTED => 'Rejected',
                        Order::PAYMENT_REFUNDED => 'Refunded',
                    ])
                    ->required(),

                Select::make('order_status')
                    ->options([
                        Order::ORDER_WAITING_PAYMENT => 'Waiting Payment',
                        Order::ORDER_PROCESSING => 'Processing',
                        Order::ORDER_COMPLETED => 'Completed',
                        Order::ORDER_CANCELLED => 'Cancelled',
                        Order::ORDER_FAILED => 'Failed',
                    ])
                    ->required(),

                Select::make('fulfillment_status')
                    ->options([
                        Order::FULFILLMENT_PENDING => 'Pending',
                        Order::FULFILLMENT_WAITING => 'Waiting',
                        Order::FULFILLMENT_PROCESSING => 'Processing',
                        Order::FULFILLMENT_SENT => 'Sent',
                        Order::FULFILLMENT_FAILED => 'Failed',
                    ])
                    ->required(),

                DateTimePicker::make('paid_at')
                    ->disabled(),

                DateTimePicker::make('fulfilled_at')
                    ->disabled(),
            ]);
    }
}
