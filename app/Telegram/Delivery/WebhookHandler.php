<?php

namespace App\Telegram\Delivery;

use App\Models\Order;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\Telegram\AdminNotificationService;
use App\Services\Telegram\DeliveryBotService;
use Exception;
use Illuminate\Support\Facades\Log;

class WebhookHandler
{
    public function __construct(
        protected DeliveryBotService $botService,
        protected ?AdminNotificationService $adminNotificationService = null
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
            'delivery_bot_started_at' => now(),
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
            $this->botService->sendActivation($chatId);
            return;
        }

        // If customer sends a message, store it into their active support ticket
        if (!empty($text)) {
            $ticket = SupportTicket::where('user_id', $user->id)
                ->whereIn('status', [SupportTicket::STATUS_OPEN, SupportTicket::STATUS_IN_PROGRESS])
                ->latest()
                ->first();

            if (!$ticket) {
                // Find user's latest fulfilled/completed order if available
                $latestOrder = Order::where('user_id', $user->id)
                    ->latest()
                    ->first();

                $ticket = SupportTicket::create([
                    'user_id' => $user->id,
                    'order_id' => $latestOrder?->id,
                    'status' => SupportTicket::STATUS_OPEN,
                ]);
            }

            SupportMessage::create([
                'support_ticket_id' => $ticket->id,
                'sender_type' => SupportMessage::SENDER_CUSTOMER,
                'message' => $text,
            ]);

            // Notify admin real-time
            try {
                if ($this->adminNotificationService) {
                    $this->adminNotificationService->notifyNewSupportTicket($ticket, $text);
                }
            } catch (Exception $e) {
                Log::warning('Failed to notify admin on support ticket: ' . $e->getMessage());
            }

            $ack = "📩 <b>Pesan Anda telah diterima oleh Admin</b>\n\n"
                . "Tim CS kami sedang mengecek laporan Anda dan akan membalas segera di chat ini.\n\n"
                . "<i>Mohon tunggu balasan kami ya kak.</i>";

            $this->botService->getClient()->sendMessage($chatId, $ack);
        }
    }

    protected function handleCallbackQuery(array $callbackQuery): void
    {
        $callbackId = $callbackQuery['id'];
        $chatId = $callbackQuery['message']['chat']['id'];
        $from = $callbackQuery['from'] ?? [];
        $data = $callbackQuery['data'] ?? '';

        $user = $this->getOrCreateUser($from);
        $this->botService->getClient()->answerCallbackQuery($callbackId);

        try {
            if (str_starts_with($data, 'delivery:login_ok:')) {
                $orderId = (int) str_replace('delivery:login_ok:', '', $data);
                $order = Order::where('user_id', $user->id)->findOrFail($orderId);
                $this->botService->sendLoginConfirmed($chatId, $order);
                return;
            }

            if (str_starts_with($data, 'delivery:problem:')) {
                $orderId = (int) str_replace('delivery:problem:', '', $data);
                $order = Order::where('user_id', $user->id)->findOrFail($orderId);

                // Create or ensure an open support ticket
                SupportTicket::firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'status' => SupportTicket::STATUS_OPEN,
                    ]
                );

                $this->botService->sendProblemPrompt($chatId, $order);
                return;
            }
        } catch (Exception $e) {
            Log::error('Delivery Webhook error: ' . $e->getMessage());
            $this->botService->getClient()->sendMessage($chatId, "⚠️ Terjadi kesalahan: " . $e->getMessage());
        }
    }
}
