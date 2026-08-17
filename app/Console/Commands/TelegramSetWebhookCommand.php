<?php

namespace App\Console\Commands;

use App\Services\Telegram\DeliveryBotService;
use App\Services\Telegram\TransactionBotService;
use Illuminate\Console\Command;

class TelegramSetWebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:set-webhook {url? : The base public URL (e.g. https://xxxx.ngrok-free.app or https://yourdomain.com)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the webhook URLs for both Transaction Bot and Delivery Bot on Telegram';

    /**
     * Execute the console command.
     */
    public function handle(TransactionBotService $transactionBot, DeliveryBotService $deliveryBot): int
    {
        $baseUrl = $this->argument('url') ?: config('app.url');

        if (empty($baseUrl) || str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
            $baseUrl = $this->ask('Masukkan URL publik HTTPS (contoh: https://xxxx.ngrok-free.app atau https://yourdomain.com)');
        }

        $baseUrl = rtrim($baseUrl, '/');

        if (!str_starts_with($baseUrl, 'https://')) {
            $this->error('Telegram Webhook memerlukan protokol HTTPS (https://...)');
            return Command::FAILURE;
        }

        $secretToken = config('services.telegram.webhook_secret');

        $this->info("Menghubungkan webhook ke basis URL: {$baseUrl}");

        // 1. Transaction Bot Webhook
        $transactionUrl = "{$baseUrl}/api/telegram/transaction/webhook";
        $this->line("1. Setting Transaction Bot Webhook: <comment>{$transactionUrl}</comment>");
        $res1 = $transactionBot->getClient()->setWebhook($transactionUrl, $secretToken);
        if ($res1['ok'] ?? false) {
            $this->info("   ✅ Transaction Bot webhook berhasil di-set: {$res1['description']}");
        } else {
            $this->warn("   ⚠️ Gagal set Transaction Bot webhook: " . ($res1['error'] ?? $res1['description'] ?? 'Periksa token bot'));
        }

        // 2. Delivery Bot Webhook
        $deliveryUrl = "{$baseUrl}/api/telegram/delivery/webhook";
        $this->line("2. Setting Delivery Bot Webhook: <comment>{$deliveryUrl}</comment>");
        $res2 = $deliveryBot->getClient()->setWebhook($deliveryUrl, $secretToken);
        if ($res2['ok'] ?? false) {
            $this->info("   ✅ Delivery Bot webhook berhasil di-set: {$res2['description']}");
        } else {
            $this->warn("   ⚠️ Gagal set Delivery Bot webhook: " . ($res2['error'] ?? $res2['description'] ?? 'Periksa token bot'));
        }

        $this->newLine();
        $this->info('Selesai! Webhook bot Telegram telah dikonfigurasi.');

        return Command::SUCCESS;
    }
}
