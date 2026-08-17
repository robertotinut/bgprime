<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Services\FulfillmentService;
use App\Services\PaymentService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->description(fn (Order $record): ?string => $record->user?->telegram_username ? "@{$record->user->telegram_username}" : "ID: {$record->user?->telegram_id}")
                    ->searchable(),

                TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::PAYMENT_PAID => 'success',
                        Order::PAYMENT_WAITING_CONFIRMATION => 'primary',
                        Order::PAYMENT_PENDING => 'warning',
                        Order::PAYMENT_REJECTED, Order::PAYMENT_REFUNDED => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('order_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::ORDER_COMPLETED => 'success',
                        Order::ORDER_PROCESSING => 'info',
                        Order::ORDER_WAITING_PAYMENT => 'warning',
                        Order::ORDER_CANCELLED, Order::ORDER_FAILED => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('fulfillment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::FULFILLMENT_SENT => 'success',
                        Order::FULFILLMENT_WAITING => 'warning',
                        Order::FULFILLMENT_PROCESSING => 'info',
                        Order::FULFILLMENT_FAILED => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->options([
                        Order::PAYMENT_PENDING => 'Pending',
                        Order::PAYMENT_WAITING_CONFIRMATION => 'Waiting Confirmation',
                        Order::PAYMENT_PAID => 'Paid',
                        Order::PAYMENT_REJECTED => 'Rejected',
                        Order::PAYMENT_REFUNDED => 'Refunded',
                    ]),

                SelectFilter::make('fulfillment_status')
                    ->options([
                        Order::FULFILLMENT_PENDING => 'Pending',
                        Order::FULFILLMENT_WAITING => 'Waiting',
                        Order::FULFILLMENT_PROCESSING => 'Processing',
                        Order::FULFILLMENT_SENT => 'Sent',
                        Order::FULFILLMENT_FAILED => 'Failed',
                    ]),
            ])
            ->recordActions([
                // 1. Approve Payment Action
                Action::make('approvePayment')
                    ->label('Approve Payment')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pembayaran')
                    ->modalDescription(fn (Order $record) => "Apakah Anda yakin ingin menyetujui pembayaran untuk invoice {$record->invoice_number}? Stok produk akan otomatis berkurang 1.")
                    ->visible(fn (Order $record): bool => in_array($record->payment_status, [
                        Order::PAYMENT_WAITING_CONFIRMATION,
                        Order::PAYMENT_PENDING,
                    ]))
                    ->action(function (Order $record): void {
                        try {
                            $paymentService = app(PaymentService::class);
                            $paymentService->approvePayment($record);

                            Notification::make()
                                ->title('Pembayaran berhasil disetujui!')
                                ->body("Order {$record->invoice_number} siap diproses fulfillment.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal menyetujui pembayaran')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // 2. Reject Payment Action
                Action::make('rejectPayment')
                    ->label('Reject Payment')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->payment_status !== Order::PAYMENT_PAID && $record->payment_status !== Order::PAYMENT_REJECTED)
                    ->action(function (Order $record): void {
                        $paymentService = app(PaymentService::class);
                        $paymentService->rejectPayment($record);

                        Notification::make()
                            ->title('Pembayaran ditolak.')
                            ->warning()
                            ->send();
                    }),

                // 3. Fulfill Order Action (Input Credential & Send)
                Action::make('fulfillOrder')
                    ->label('Send Credential')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn (Order $record): bool => $record->payment_status === Order::PAYMENT_PAID && $record->fulfillment_status !== Order::FULFILLMENT_SENT)
                    ->form([
                        TextInput::make('username')
                            ->label('Email / Username Akun')
                            ->required(),

                        TextInput::make('password')
                            ->label('Password Akun')
                            ->required(),

                        Textarea::make('notes')
                            ->label('Catatan Tambahan untuk Customer')
                            ->placeholder('Contoh: Jangan ganti email, garansi 30 hari.')
                            ->default('Akses premium aktif. Harap simpan data login ini dengan baik.'),
                    ])
                    ->action(function (Order $record, array $data): void {
                        try {
                            $fulfillmentService = app(FulfillmentService::class);
                            $fulfillment = $fulfillmentService->fulfillOrder(
                                order: $record,
                                username: $data['username'],
                                password: $data['password'],
                                notes: $data['notes'] ?? null
                            );

                            if ($fulfillment->send_status === 'sent') {
                                Notification::make()
                                    ->title('Kredensial berhasil dikirim!')
                                    ->body("Akun telah dikirim ke Telegram pembeli untuk invoice {$record->invoice_number}.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Kredensial tersimpan tapi gagal terkirim ke Telegram')
                                    ->body('Pastikan token bot valid dan user sudah klik start di Delivery Bot.')
                                    ->warning()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal memproses fulfillment')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // 4. Resend Credential Action
                Action::make('resendCredential')
                    ->label('Resend Credential')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->fulfillment !== null && $record->fulfillment_status === Order::FULFILLMENT_SENT)
                    ->action(function (Order $record): void {
                        try {
                            $fulfillmentService = app(FulfillmentService::class);
                            $fulfillmentService->resendCredential($record->fulfillment);

                            Notification::make()
                                ->title('Kredensial berhasil dikirim ulang!')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal mengirim ulang kredensial')
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
