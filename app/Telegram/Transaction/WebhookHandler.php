<?php

namespace App\Telegram\Transaction;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\Telegram\TransactionBotService;
use Exception;
use Illuminate\Support\Facades\Log;

class WebhookHandler
{
    public function __construct(
        protected TransactionBotService $botService,
        protected OrderService $orderService,
        protected PaymentService $paymentService
    ) {}

    public function handle(array $update): void
    {
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
        }
    }

    protected function getOrCreateUser(array $from): User
    {
        $telegramId = $from['id'];
        $user = User::where('telegram_id', $telegramId)->first();

        $data = [
            'telegram_username' => $from['username'] ?? null,
            'first_name' => $from['first_name'] ?? null,
            'last_name' => $from['last_name'] ?? null,
            'transaction_bot_started_at' => now(),
        ];

        if (!$user) {
            $fullName = trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? '')) ?: ($from['username'] ?? "Telegram_{$telegramId}");
            $data['name'] = $fullName;
            $data['telegram_id'] = $telegramId;
            return User::create($data);
        }

        $user->update($data);
        return $user;
    }

    protected function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $from = $message['from'] ?? [];
        if (empty($from['id'])) {
            return;
        }

        $user = $this->getOrCreateUser($from);
        $text = trim($message['text'] ?? '');

        if (str_starts_with($text, '/start')) {
            $parts = explode(' ', $text, 2);
            $startParam = $parts[1] ?? null;

            if ($startParam && str_starts_with($startParam, 'product_')) {
                $productId = (int) str_replace('product_', '', $startParam);
                $product = Product::find($productId);

                if ($product) {
                    $this->botService->sendProductDetail($chatId, $product);
                    return;
                }
            }

            $this->botService->sendWelcome($chatId);
            return;
        }

        if (in_array($text, ['/menu', '/katalog', '🛍️ Katalog Produk'])) {
            $this->botService->sendCatalog($chatId);
            return;
        }

        if (in_array($text, ['/pesanan', '📦 Pesanan Saya'])) {
            $this->botService->sendMyOrders($chatId, $user);
            return;
        }

        if (in_array($text, ['/cara_bayar', '💳 Cara Pembayaran'])) {
            $this->botService->sendPaymentGuide($chatId);
            return;
        }

        if (in_array($text, ['/bantuan', '🆘 Bantuan'])) {
            $this->botService->sendHelpMenu($chatId);
            return;
        }

        // Default response if message not recognized
        $this->botService->sendWelcome($chatId);
    }

    protected function handleCallbackQuery(array $callbackQuery): void
    {
        $callbackId = $callbackQuery['id'];
        $chatId = $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'] ?? null;
        $from = $callbackQuery['from'] ?? [];
        $data = $callbackQuery['data'] ?? '';

        $user = $this->getOrCreateUser($from);
        $this->botService->getClient()->answerCallbackQuery($callbackId);

        try {
            if ($data === 'menu:main') {
                $this->botService->sendWelcome($chatId, $messageId);
                return;
            }

            if ($data === 'menu:catalog') {
                $this->botService->sendCatalog($chatId, $messageId);
                return;
            }

            if ($data === 'menu:my_orders') {
                $this->botService->sendMyOrders($chatId, $user, $messageId);
                return;
            }

            if ($data === 'menu:payment_guide') {
                $this->botService->sendPaymentGuide($chatId, $messageId);
                return;
            }

            if ($data === 'menu:help') {
                $this->botService->sendHelpMenu($chatId, $messageId);
                return;
            }

            if (str_starts_with($data, 'category:')) {
                $categoryId = str_replace('category:', '', $data);
                $this->botService->sendCategoryProducts($chatId, $categoryId, $messageId);
                return;
            }

            if (str_starts_with($data, 'product:')) {
                $productId = (int) str_replace('product:', '', $data);
                $product = Product::findOrFail($productId);
                $this->botService->sendProductDetail($chatId, $product, $messageId);
                return;
            }

            if (str_starts_with($data, 'checkout:')) {
                $productId = (int) str_replace('checkout:', '', $data);
                $product = Product::findOrFail($productId);
                $this->botService->sendCheckoutConfirmation($chatId, $product, $messageId);
                return;
            }

            if (str_starts_with($data, 'create_order:')) {
                $productId = (int) str_replace('create_order:', '', $data);
                $product = Product::findOrFail($productId);
                $order = $this->orderService->createOrder($user, $product);
                $this->botService->sendInvoice($chatId, $order);
                return;
            }

            if (str_starts_with($data, 'paid:')) {
                $orderId = (int) str_replace('paid:', '', $data);
                $order = Order::where('user_id', $user->id)->findOrFail($orderId);
                $order = $this->paymentService->confirmPayment($order);
                $this->botService->sendPaymentWaitingConfirmation($chatId, $order, $messageId);
                return;
            }

            if (str_starts_with($data, 'cancel_order:')) {
                $orderId = (int) str_replace('cancel_order:', '', $data);
                $order = Order::where('user_id', $user->id)->findOrFail($orderId);
                $this->orderService->cancelOrder($order);

                $text = "❌ <b>Pesanan Telah Dibatalkan</b>\n\n"
                    . "Invoice: <code>{$order->invoice_number}</code>\n"
                    . "Status: Dibatalkan.";

                $keyboard = [
                    'inline_keyboard' => [
                        [['text' => '🛍️ Pilih Produk Lain', 'callback_data' => 'menu:catalog']],
                        [['text' => '🏠 Menu Utama', 'callback_data' => 'menu:main']],
                    ],
                ];

                $this->botService->getClient()->editMessageText($chatId, $messageId, $text, $keyboard);
                return;
            }

            if (str_starts_with($data, 'order_detail:')) {
                $orderId = (int) str_replace('order_detail:', '', $data);
                $order = Order::where('user_id', $user->id)->findOrFail($orderId);
                $this->botService->sendOrderDetail($chatId, $order, $messageId);
                return;
            }

            if (str_starts_with($data, 'help:')) {
                $topic = str_replace('help:', '', $data);
                $this->botService->sendHelpDetail($chatId, $topic, $messageId);
                return;
            }
        } catch (Exception $e) {
            Log::error('Transaction Webhook error: ' . $e->getMessage());
            $this->botService->getClient()->sendMessage($chatId, "⚠️ Terjadi kesalahan: " . $e->getMessage());
        }
    }
}
