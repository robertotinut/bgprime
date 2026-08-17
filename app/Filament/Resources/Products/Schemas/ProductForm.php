<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('duration_label')
                    ->label('Duration Label')
                    ->default('30 Hari')
                    ->required(),

                Select::make('fulfillment_type')
                    ->label('Mode Pengiriman Akun')
                    ->options([
                        'manual' => '🛒 Reseller Mode (On-Demand / Manual Input)',
                        'instant' => '⚡ Instant Mode (Auto-Delivery dari Pool Stok Akun)',
                    ])
                    ->default('manual')
                    ->helperText('Pilih Instant jika Anda sudah memiliki stok akun dan ingin bot mengirim otomatis saat pembayaran disetujui.')
                    ->required(),

                TextInput::make('price')
                    ->label('Price (IDR)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                TextInput::make('stock_qty')
                    ->label('Stock Quantity')
                    ->numeric()
                    ->default(0)
                    ->required(),

                TextInput::make('low_stock_threshold')
                    ->label('Low Stock Alert Threshold')
                    ->numeric()
                    ->default(2)
                    ->required(),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
