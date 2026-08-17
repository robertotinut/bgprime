<?php

use App\Http\Controllers\Telegram\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('telegram')->group(function () {
    Route::post('/transaction/webhook', [WebhookController::class, 'handleTransaction']);
    Route::post('/delivery/webhook', [WebhookController::class, 'handleDelivery']);
});
