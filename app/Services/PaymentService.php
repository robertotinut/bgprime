<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCredential;
use App\Services\Telegram\AdminNotificationService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        protected StockService $stockService,
        protected FulfillmentService $fulfillmentService,
        protected AdminNotificationService $adminNotificationService
    ) {}

    /**
     * Customer marks payment as made ("Saya Sudah Bayar").
     */
    public function confirmPayment(Order $order): Order
    {
        if ($order->payment_status === Order::PAYMENT_PENDING) {
            $order->update([
                'payment_status' => Order::PAYMENT_WAITING_CONFIRMATION,
            ]);

            // Real-time notification to Telegram Admin
            try {
                $this->adminNotificationService->notifyPaymentConfirmation($order);
            } catch (Exception $e) {
                Log::warning('Failed to notify admin on payment confirmation: ' . $e->getMessage());
            }
        }

        return $order->fresh();
    }

    /**
     * Admin approves the payment.
     * Decrements product stock atomically and idempotently.
     * If product is INSTANT mode: automatically dispatches credential from pool.
     * If product is MANUAL mode: marks as waiting fulfillment for reseller on-demand workflow.
     *
     * @throws Exception
     */
    public function approvePayment(Order $order): Order
    {
        // Idempotency check: if already paid, do not re-process
        if ($order->payment_status === Order::PAYMENT_PAID) {
            return $order;
        }

        return DB::transaction(function () use ($order) {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->payment_status === Order::PAYMENT_PAID) {
                return $lockedOrder;
            }

            // Reduce stock
            $this->stockService->reduceStockForOrder($lockedOrder);

            // Check if product is in Instant Auto-Delivery mode
            $product = $lockedOrder->product;
            $autoFulfillSuccess = false;

            if ($product && $product->isInstant()) {
                /** @var ProductCredential|null $availableCredential */
                $availableCredential = ProductCredential::where('product_id', $product->id)
                    ->where('is_used', false)
                    ->lockForUpdate()
                    ->first();

                if ($availableCredential) {
                    $availableCredential->update([
                        'is_used' => true,
                        'order_id' => $lockedOrder->id,
                        'used_at' => now(),
                    ]);

                    // Update order statuses
                    $lockedOrder->update([
                        'payment_status' => Order::PAYMENT_PAID,
                        'order_status' => Order::ORDER_PROCESSING,
                        'fulfillment_status' => Order::FULFILLMENT_WAITING,
                        'paid_at' => now(),
                    ]);

                    // Instant dispatch
                    $this->fulfillmentService->fulfillOrder(
                        order: $lockedOrder,
                        username: $availableCredential->username,
                        password: $availableCredential->password,
                        notes: $availableCredential->notes
                    );

                    $autoFulfillSuccess = true;
                }
            }

            if (!$autoFulfillSuccess) {
                // Manual Reseller Mode: Waiting for admin to obtain account from supplier
                $lockedOrder->update([
                    'payment_status' => Order::PAYMENT_PAID,
                    'order_status' => Order::ORDER_PROCESSING,
                    'fulfillment_status' => Order::FULFILLMENT_WAITING,
                    'paid_at' => now(),
                ]);
            }

            return $lockedOrder->fresh();
        });
    }

    /**
     * Admin rejects the payment.
     */
    public function rejectPayment(Order $order): Order
    {
        $order->update([
            'payment_status' => Order::PAYMENT_REJECTED,
            'order_status' => Order::ORDER_FAILED,
        ]);

        return $order->fresh();
    }

    /**
     * Admin refunds the payment and restores stock if needed.
     *
     * @throws Exception
     */
    public function refundPayment(Order $order, string $reason = 'Manual refund by admin'): Order
    {
        return DB::transaction(function () use ($order, $reason) {
            $this->stockService->restoreStockForOrder($order, $reason);

            $order->update([
                'payment_status' => Order::PAYMENT_REFUNDED,
                'order_status' => Order::ORDER_CANCELLED,
            ]);

            return $order->fresh();
        });
    }
}
