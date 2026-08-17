<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Telegram\Delivery\WebhookHandler as DeliveryWebhookHandler;
use App\Telegram\Transaction\WebhookHandler as TransactionWebhookHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle incoming updates for Transaction Bot.
     */
    public function handleTransaction(Request $request, TransactionWebhookHandler $handler): JsonResponse
    {
        if (!$this->isAuthorizedWebhook($request)) {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $payload = $request->all();
        try {
            $handler->handle($payload);
        } catch (\Throwable $e) {
            Log::error('Error processing Transaction Bot webhook:', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle incoming updates for Delivery Bot.
     */
    public function handleDelivery(Request $request, DeliveryWebhookHandler $handler): JsonResponse
    {
        if (!$this->isAuthorizedWebhook($request)) {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $payload = $request->all();
        try {
            $handler->handle($payload);
        } catch (\Throwable $e) {
            Log::error('Error processing Delivery Bot webhook:', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Verify Telegram Webhook Secret header if configured.
     */
    protected function isAuthorizedWebhook(Request $request): bool
    {
        $secret = config('services.telegram.webhook_secret');
        if (empty($secret)) {
            return true;
        }

        $headerSecret = $request->header('X-Telegram-Bot-Api-Secret-Token');
        return hash_equals($secret, (string) $headerSecret);
    }
}
