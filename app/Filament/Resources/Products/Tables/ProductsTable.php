<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('info'),

                TextColumn::make('fulfillment_type')
                    ->label('Mode')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'instant' ? '⚡ Instant' : '🛒 Reseller')
                    ->color(fn ($state) => $state === 'instant' ? 'success' : 'warning'),

                TextColumn::make('duration_label')
                    ->label('Duration'),

                TextColumn::make('price')
                    ->label('Price')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('stock_qty')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->color(fn (Product $record): string => $record->isLowStock() ? 'danger' : 'success'),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
            ])
            ->recordActions([
                Action::make('adjustStock')
                    ->label('Adjust Stock')
                    ->icon('heroicon-o-arrows-up-down')
                    ->color('warning')
                    ->form([
                        Select::make('type')
                            ->options([
                                StockMovement::TYPE_MANUAL_ADD => 'Tambah Stok (+)',
                                StockMovement::TYPE_MANUAL_REDUCE => 'Kurang Stok (-)',
                                StockMovement::TYPE_ADJUSTMENT => 'Set Stok Baru (=)',
                            ])
                            ->default(StockMovement::TYPE_MANUAL_ADD)
                            ->required(),

                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->label('Jumlah / Angka Stok'),

                        Textarea::make('notes')
                            ->label('Catatan Perubahan')
                            ->placeholder('Contoh: Restock supplier baru'),
                    ])
                    ->action(function (Product $record, array $data): void {
                        try {
                            $stockService = app(StockService::class);
                            $stockService->adjustStock(
                                product: $record,
                                quantity: (int) $data['quantity'],
                                type: $data['type'],
                                notes: $data['notes'] ?? null,
                                createdBy: auth()->id()
                            );

                            Notification::make()
                                ->title('Stok berhasil diperbarui!')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal mengubah stok')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
