<?php

namespace App\Filament\Pages;

use App\Services\Telegram\ChannelService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PublishStock extends Page
{
    protected static ?string $navigationLabel = 'Publish Stock';

    protected static ?string $title = 'Ready Stock Publisher';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.publish-stock';

    public string $previewText = '';

    public array $products = [];

    public function mount(): void
    {
        $this->refreshPreview();
    }

    public function refreshPreview(): void
    {
        $channelService = app(ChannelService::class);
        $content = $channelService->generateReadyStockContent();
        $this->previewText = $content['text'];
    }

    public function publish(): void
    {
        try {
            $channelService = app(ChannelService::class);
            $result = $channelService->publishReadyStock();

            if ($result['ok'] ?? false) {
                Notification::make()
                    ->title('Berhasil mempublikasikan stok ke Channel Telegram!')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Gagal mengirim ke Channel')
                    ->body('Pastikan token bot valid, channel ID benar, dan bot sudah ditambahkan sebagai Administrator di channel.')
                    ->warning()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Terjadi kesalahan')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publishToChannel')
                ->label('📢 Publish to Telegram Channel')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Publikasi Ready Stock')
                ->modalDescription('Pesan daftar produk dan tombol order akan dikirimkan ke Telegram Channel resmi sekarang.')
                ->action(fn () => $this->publish()),
        ];
    }
}
