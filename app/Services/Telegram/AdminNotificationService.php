<?php

namespace App\Services\Telegram;

use App\Models\Order;
use App\Models\Product;
use App\Models\SupportTicket;
use Exception;
use Illuminate\Support\Facades\Log;

class AdminNotificationService
{
    protected TelegramClient $client;
    protected ?string $adminChatId;

    public function __construct()
    {
        // Use Transaction Bot token to send notifications to Admin
        $token = config('services.telegram.transaction_bot_token', '');
        $this->client = new TelegramClient($token);
        $this->adminChatId = config('services.telegram.admin_chat_id');
    }

    /**
     * Send real-time notification to admin when customer confirms payment.
     */
    public function notifyPaymentConfirmation(Order $order): bool
    {
        if (empty($this->adminChatId)) {
            return false;
        }

        $user = $order->user;
        $customerUsername = $user?->telegram_username ? "@{$user->telegram_username}" : "ID: {$user?->telegram_id}";
        $customerName = e($user?->name ?? 'Customer');
        $mode = $order->product?->isInstant() ? '⚡ Instant (Auto Delivery)' : '🛒 Reseller (On-Demand / Manual Input)';

        $text = "🔔 <b>PEMBAYARAN BARU MASUK!</b>\n\n"
            . "<b>Invoice:</b> <code>{$order->invoice_number}</code>\n"
            . "<b>Produk:</b> {$order->product_name}\n"
            . "<b>Total:</b> <b>{$order->formatted_amount}</b>\n"
            . "<b>Customer:</b> {$customerName} ({$customerUsername})\n"
            . "<b>Mode Produk:</b> {$mode}\n\n"
            . "👉 <i>Customer telah klik 'Saya Sudah Bayar'. Silakan cek mutasi dan setujui (Approve) di Admin Panel.</i>";

        try {
            $response = $this->client->sendMessage($this->adminChatId, $text);
            return (bool) ($response['ok'] ?? false);
        } catch (Exception $e) {
            Log::error('Failed to send admin notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification when a customer submits a complaint / support ticket message.
     */
    public function notifyNewSupportTicket(SupportTicket $ticket, string $message): bool
    {
        if (empty($this->adminChatId)) {
            return false;
        }

        $user = $ticket->user;
        $customerInfo = $user?->telegram_username ? "@{$user->telegram_username}" : "ID: {$user?->telegram_id}";
        $orderInvoice = $ticket->order ? "<code>{$ticket->order->invoice_number}</code>" : 'Non-Order';

        $text = "🆘 <b>TIKET BANTUAN CUSTOMER BARU</b>\n\n"
            . "<b>Tiket ID:</b> #{$ticket->id}\n"
            . "<b>Customer:</b> {$customerInfo}\n"
            . "<b>Pesanan:</b> {$orderInvoice}\n\n"
            . "<b>Pesan Customer:</b>\n"
            . "<i>\"" . e($message) . "\"</i>\n\n"
            . "👉 <i>Silakan balas melalui menu Support Tickets di Admin Panel.</i>";

        try {
            $response = $this->client->sendMessage($this->adminChatId, $text);
            return (bool) ($response['ok'] ?? false);
        } catch (Exception $e) {
            Log::error('Failed to send support notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send low stock alert to admin.
     */
    public function notifyLowStock(Product $product): bool
    {
        if (empty($this->adminChatId)) {
            return false;
        }

        $text = "⚠️ <b>PERINGATAN STOK MENIPIS!</b>\n\n"
            . "<b>Produk:</b> {$product->name}\n"
            . "<b>Sisa Stok:</b> {$product->stock_qty} (Batas: {$product->low_stock_threshold})\n\n"
            . "👉 <i>Harap segera lakukan restock akun agar penjualan tetap berjalan.</i>";

        try {
            $response = $this->client->sendMessage($this->adminChatId, $text);
            return (bool) ($response['ok'] ?? false);
        } catch (Exception $e) {
            Log::error('Failed to send low stock notification: ' . $e->getMessage());
            return false;
        }
    }
}
