<?php

namespace App\Filament\Resources\SupportTickets\Tables;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Services\Telegram\DeliveryBotService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupportTicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('Ticket #')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->description(fn (SupportTicket $record): ?string => $record->user?->telegram_username ? "@{$record->user->telegram_username}" : "ID: {$record->user?->telegram_id}")
                    ->searchable(),

                TextColumn::make('order.invoice_number')
                    ->label('Related Order')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('latest_message')
                    ->label('Latest Message')
                    ->state(fn (SupportTicket $record): string => $record->messages()->latest()->first()?->message ?? 'No message')
                    ->limit(40),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        SupportTicket::STATUS_OPEN => 'danger',
                        SupportTicket::STATUS_IN_PROGRESS => 'warning',
                        SupportTicket::STATUS_CLOSED => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        SupportTicket::STATUS_OPEN => 'Open',
                        SupportTicket::STATUS_IN_PROGRESS => 'In Progress',
                        SupportTicket::STATUS_CLOSED => 'Closed',
                    ]),
            ])
            ->recordActions([
                Action::make('reply')
                    ->label('Balas Pesan')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('primary')
                    ->form([
                        Textarea::make('reply_message')
                            ->label('Pesan Balasan Admin')
                            ->placeholder('Tuliskan respon bantuan untuk customer...')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (SupportTicket $record, array $data): void {
                        $reply = $data['reply_message'];

                        // Create support message in db
                        SupportMessage::create([
                            'support_ticket_id' => $record->id,
                            'sender_type' => SupportMessage::SENDER_ADMIN,
                            'message' => $reply,
                        ]);

                        $record->update(['status' => SupportTicket::STATUS_IN_PROGRESS]);

                        // Send message via Delivery Bot
                        try {
                            $deliveryBotService = app(DeliveryBotService::class);
                            $sent = $deliveryBotService->sendAdminReply($record, $reply);

                            if ($sent) {
                                Notification::make()
                                    ->title('Balasan terkirim ke customer!')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Balasan tersimpan, namun bot belum terkirim')
                                    ->body('Pastikan token Delivery Bot valid dan customer terdaftar.')
                                    ->warning()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal mengirim balasan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('closeTicket')
                    ->label('Tutup Tiket')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (SupportTicket $record): bool => $record->status !== SupportTicket::STATUS_CLOSED)
                    ->action(function (SupportTicket $record): void {
                        $record->update(['status' => SupportTicket::STATUS_CLOSED]);
                        Notification::make()
                            ->title('Tiket ditutup.')
                            ->success()
                            ->send();
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
