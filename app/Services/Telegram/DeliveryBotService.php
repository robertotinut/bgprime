<?php

namespace App\Services\Telegram;

use App\Models\Order;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;

class DeliveryBotService
{
    protected TelegramClient $client;

    public function __construct()
    {
        $token = config('services.telegram.delivery_bot_token', '');
        $this->client = new TelegramClient($token);
    }

    public function getClient(): TelegramClient
    {
        return $this->client;
    }

    public function sendActivation(int|string $chatId): array
    {
        $text = "👋 <b>Selamat datang di Delivery Bot.</b>\n\n"
            . "Bot ini digunakan untuk menerima informasi akun & kredensial dari pesanan Anda secara aman.\n\n"
            . "✅ <b>Delivery Bot Anda sudah aktif.</b>\n\n"
            . "Jika Anda memiliki pesanan yang sudah dibayar, detail akun akan dikirimkan langsung ke chat ini.";

        $transactionBotUsername = config('services.telegram.transaction_bot_username', 'transaction_bot');
        $keyboard = [
            'inline_keyboard' => [
                [['text' => '🛍️ Ke Transaction Bot', 'url' => "https://t.me/{$transactionBotUsername}"]],
            ],
        ];

        return $this->client->sendMessage($chatId, $text, $keyboard);
    }

    public function sendCredential(Order $order, string $username, string $password, ?string $notes = null): bool
    {
        $user = $order->user;
        if (!$user || !$user->telegram_id) {
            return false;
        }

        $notesText = $notes ? e($notes) : 'Simpan informasi akun ini dengan baik dan jangan dibagikan.';

        $text = "✅ <b>PESANANMU SUDAH SIAP</b>\n\n"
            . "<b>Invoice:</b> <code>{$order->invoice_number}</code>\n"
            . "<b>Produk:</b> {$order->product_name}\n\n"
            . "📧 <b>Email / Username:</b>\n<code>" . e($username) . "</code>\n\n"
            . "🔑 <b>Password:</b>\n<code>" . e($password) . "</code>\n\n"
            . "📝 <b>Catatan:</b>\n{$notesText}\n\n"
            . "Terima kasih sudah order ❤️";

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '✅ SUDAH BISA LOGIN', 'callback_data' => "delivery:login_ok:{$order->id}"]],
                [['text' => '❓ ADA MASALAH', 'callback_data' => "delivery:problem:{$order->id}"]],
            ],
        ];

        $response = $this->client->sendMessage($user->telegram_id, $text, $keyboard);

        return (bool) ($response['ok'] ?? false);
    }

    public function sendProblemPrompt(int|string $chatId, Order $order): array
    {
        $text = "❓ <b>Laporan Kendala Akun</b>\n\n"
            . "<b>Invoice:</b> <code>{$order->invoice_number}</code>\n"
            . "<b>Produk:</b> {$order->product_name}\n\n"
            . "Silakan jelaskan kendala yang Anda alami (contoh: <i>password salah, butuh verifikasi email, limit akun, dll</i>).\n\n"
            . "Ketik dan kirimkan pesan Anda di chat ini sekarang:";

        return $this->client->sendMessage($chatId, $text);
    }

    public function sendLoginConfirmed(int|string $chatId, Order $order): array
    {
        $text = "🎉 <b>Alhamdulillah, senang mendengarnya!</b>\n\n"
            . "Terima kasih atas konfirmasinya untuk pesanan <code>{$order->invoice_number}</code> ({$order->product_name}).\n\n"
            . "Selamat menggunakan produk Anda. Jangan ragu hubungi kami jika ada pertanyaan.";

        return $this->client->sendMessage($chatId, $text);
    }

    public function sendAdminReply(SupportTicket $ticket, string $replyMessage): bool
    {
        $user = $ticket->user;
        if (!$user || !$user->telegram_id) {
            return false;
        }

        $orderInfo = $ticket->order ? " (Invoice: <code>{$ticket->order->invoice_number}</code>)" : '';

        $text = "💬 <b>Pesan dari Admin{$orderInfo}</b>\n\n"
            . e($replyMessage) . "\n\n"
            . "<i>Anda dapat membalas pesan ini langsung jika masih membutuhkan bantuan.</i>";

        $response = $this->client->sendMessage($user->telegram_id, $text);

        return (bool) ($response['ok'] ?? false);
    }
}
