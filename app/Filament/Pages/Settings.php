<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'Store & Bot Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'admin_chat_id' => config('services.telegram.admin_chat_id'),
            'channel_id' => config('services.telegram.channel_id'),
            'transaction_bot_username' => config('services.telegram.transaction_bot_username'),
            'delivery_bot_username' => config('services.telegram.delivery_bot_username'),
            'qris_image_path' => config('services.telegram.qris_image_path'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('QRIS Payment Barcode')
                    ->description('Upload barcode QRIS yang akan ditampilkan kepada customer saat checkout')
                    ->schema([
                        FileUpload::make('qris_image')
                            ->label('Upload Gambar QRIS')
                            ->image()
                            ->disk('public')
                            ->directory('qris')
                            ->preserveFilenames(),
                    ]),

                Section::make('Telegram Bot & Channel IDs')
                    ->description('Informasi koneksi bot Telegram dan channel resmi')
                    ->schema([
                        TextInput::make('admin_chat_id')
                            ->label('Telegram Admin Chat ID')
                            ->helperText('ID chat Telegram Anda/grup admin untuk menerima alert real-time order baru dan pembayaran.')
                            ->disabled(),

                        TextInput::make('channel_id')
                            ->label('Telegram Channel ID')
                            ->disabled(),

                        TextInput::make('transaction_bot_username')
                            ->label('Transaction Bot Username')
                            ->prefix('@')
                            ->disabled(),

                        TextInput::make('delivery_bot_username')
                            ->label('Delivery Bot Username')
                            ->prefix('@')
                            ->disabled(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if (!empty($data['qris_image'])) {
            Notification::make()
                ->title('Pengaturan QRIS berhasil diperbarui!')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Pengaturan tersimpan.')
                ->success()
                ->send();
        }
    }
}
