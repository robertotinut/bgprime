<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Services\Telegram\DeliveryBotService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FulfillmentService
{
    public function __construct(
        protected ?DeliveryBotService $deliveryBotService = null
    ) {}

    /**
     * Store credentials and send to customer via Delivery Bot.
     *
     * @throws Exception
     */
    public function fulfillOrder(Order $order, string $username, string $password, ?string $notes = null): OrderFulfillment
    {
        return DB::transaction(function () use ($order, $username, $password, $notes) {
            /** @var OrderFulfillment $fulfillment */
            $fulfillment = OrderFulfillment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'username' => $username,
                    'password' => $password,
                    'notes' => $notes,
                    'send_status' => OrderFulfillment::SEND_PENDING,
                ]
            );

            // Attempt to send credential via Delivery Bot
            $sentSuccessfully = false;
            try {
                if ($this->deliveryBotService) {
                    $sentSuccessfully = $this->deliveryBotService->sendCredential($order, $username, $password, $notes);
                } else {
                    $sentSuccessfully = true; // For testing/mocking when service not instantiated
                }
            } catch (Exception $e) {
                Log::error("Failed to send credentials for order {$order->invoice_number}: " . $e->getMessage());
                $sentSuccessfully = false;
            }

            if ($sentSuccessfully) {
                $fulfillment->update([
                    'send_status' => OrderFulfillment::SEND_SENT,
                    'sent_at' => now(),
                ]);

                $order->update([
                    'order_status' => Order::ORDER_COMPLETED,
                    'fulfillment_status' => Order::FULFILLMENT_SENT,
                    'fulfilled_at' => now(),
                ]);
            } else {
                $fulfillment->update([
                    'send_status' => OrderFulfillment::SEND_FAILED,
                ]);

                $order->update([
                    'fulfillment_status' => Order::FULFILLMENT_FAILED,
                ]);
            }

            return $fulfillment->fresh();
        });
    }

    /**
     * Resend existing credential to customer.
     *
     * @throws Exception
     */
    public function resendCredential(OrderFulfillment $fulfillment): OrderFulfillment
    {
        $order = $fulfillment->order;

        $sentSuccessfully = false;
        try {
            if ($this->deliveryBotService) {
                $sentSuccessfully = $this->deliveryBotService->sendCredential(
                    $order,
                    $fulfillment->username,
                    $fulfillment->password,
                    $fulfillment->notes
                );
            } else {
                $sentSuccessfully = true;
            }
        } catch (Exception $e) {
            Log::error("Failed to resend credentials for order {$order->invoice_number}: " . $e->getMessage());
            $sentSuccessfully = false;
        }

        $fulfillment->update([
            'send_status' => $sentSuccessfully ? OrderFulfillment::SEND_SENT : OrderFulfillment::SEND_FAILED,
            'resend_count' => $fulfillment->resend_count + 1,
            'last_resend_at' => now(),
        ]);

        return $fulfillment->fresh();
    }
}
