<?php

namespace App\Services\Telegram;

use App\Models\Product;

class ChannelService
{
    protected TelegramClient $client;
    protected string $channelId;
    protected string $transactionBotUsername;

    public function __construct()
    {
        // Channel broadcasting uses the Transaction bot token
        $token = config('services.telegram.transaction_bot_token', '');
        $this->client = new TelegramClient($token);
        $this->channelId = config('services.telegram.channel_id', '');
        $this->transactionBotUsername = config('services.telegram.transaction_bot_username', 'transaction_bot');
    }

    /**
     * Generate formatted ready stock message text and keyboard.
     */
    public function generateReadyStockContent(): array
    {
        $products = Product::where('is_active', true)
            ->where('stock_qty', '>', 0)
            ->orderBy('sort_order')
            ->get();

        if ($products->isEmpty()) {
            return [
                'text' => "🔥 <b>READY STOCK HARI INI</b>\n\nSaat ini semua produk sedang sold out. Pantau terus channel ini untuk info restock!",
                'keyboard' => null,
            ];
        }

        $lines = ["🔥 <b>READY STOCK HARI INI</b>\n"];
        $buttons = [];

        foreach ($products as $product) {
            $lines[] = "⚡ <b>{$product->name}</b> ({$product->duration_label})\n"
                . "💰 <b>{$product->formatted_price}</b>\n"
                . "📦 Stock: {$product->stock_qty}\n";

            $deepLink = "https://t.me/{$this->transactionBotUsername}?start=product_{$product->id}";
            $buttons[] = [
                ['text' => "🛒 Beli {$product->name}", 'url' => $deepLink],
            ];
        }

        $lines[] = "👇 <i>Klik tombol di bawah untuk order langsung:</i>";

        return [
            'text' => implode("\n", $lines),
            'keyboard' => ['inline_keyboard' => $buttons],
        ];
    }

    /**
     * Publish ready stock to the configured Telegram Channel.
     */
    public function publishReadyStock(): array
    {
        $content = $this->generateReadyStockContent();

        return $this->client->sendMessage(
            $this->channelId,
            $content['text'],
            $content['keyboard']
        );
    }
}
