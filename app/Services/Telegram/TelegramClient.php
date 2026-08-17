<?php

namespace App\Services\Telegram;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramClient
{
    public function __construct(
        protected ?string $botToken = null
    ) {}

    public function setToken(string $botToken): self
    {
        $this->botToken = $botToken;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->botToken;
    }

    protected function getBaseUrl(): string
    {
        if (empty($this->botToken)) {
            throw new Exception("Telegram Bot Token belum dikonfigurasi.");
        }

        return "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Send a text message to a chat.
     */
    public function sendMessage(
        int|string $chatId,
        string $text,
        ?array $replyMarkup = null,
        string $parseMode = 'HTML',
        bool $disableWebPagePreview = true
    ): array {
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
            'disable_web_page_preview' => $disableWebPagePreview,
        ];

        if ($replyMarkup !== null) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        return $this->sendRequest('sendMessage', $payload);
    }

    /**
     * Send a photo (by local path or URL or file ID) to a chat.
     */
    public function sendPhoto(
        int|string $chatId,
        string $photoPathOrUrl,
        string $caption = '',
        ?array $replyMarkup = null,
        string $parseMode = 'HTML'
    ): array {
        $endpoint = $this->getBaseUrl() . '/sendPhoto';

        if (file_exists($photoPathOrUrl)) {
            $request = Http::attach(
                'photo',
                file_get_contents($photoPathOrUrl),
                basename($photoPathOrUrl)
            );

            $data = [
                'chat_id' => $chatId,
                'caption' => $caption,
                'parse_mode' => $parseMode,
            ];

            if ($replyMarkup !== null) {
                $data['reply_markup'] = json_encode($replyMarkup);
            }

            $response = $request->post($endpoint, $data);
            return $response->json() ?? [];
        }

        $payload = [
            'chat_id' => $chatId,
            'photo' => $photoPathOrUrl,
            'caption' => $caption,
            'parse_mode' => $parseMode,
        ];

        if ($replyMarkup !== null) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        return $this->sendRequest('sendPhoto', $payload);
    }

    /**
     * Edit message text.
     */
    public function editMessageText(
        int|string $chatId,
        int $messageId,
        string $text,
        ?array $replyMarkup = null,
        string $parseMode = 'HTML',
        bool $disableWebPagePreview = true
    ): array {
        $payload = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => $parseMode,
            'disable_web_page_preview' => $disableWebPagePreview,
        ];

        if ($replyMarkup !== null) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        return $this->sendRequest('editMessageText', $payload);
    }

    /**
     * Answer callback query to dismiss loading state in Telegram client.
     */
    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null, bool $showAlert = false): array
    {
        $payload = [
            'callback_query_id' => $callbackQueryId,
            'show_alert' => $showAlert,
        ];

        if ($text !== null) {
            $payload['text'] = $text;
        }

        return $this->sendRequest('answerCallbackQuery', $payload);
    }

    /**
     * Set webhook for this bot.
     */
    public function setWebhook(string $url, ?string $secretToken = null): array
    {
        $payload = ['url' => $url];
        if ($secretToken) {
            $payload['secret_token'] = $secretToken;
        }

        return $this->sendRequest('setWebhook', $payload);
    }

    /**
     * Internal request sender.
     */
    protected function sendRequest(string $method, array $data): array
    {
        try {
            $response = Http::timeout(10)->post("{$this->getBaseUrl()}/{$method}", $data);
            $result = $response->json();

            if (!$response->successful() || !($result['ok'] ?? false)) {
                Log::warning("Telegram API error [{$method}]:", [
                    'response' => $result,
                    'status' => $response->status(),
                ]);
            }

            return $result ?? ['ok' => false];
        } catch (Exception $e) {
            Log::error("Telegram API exception [{$method}]: " . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
