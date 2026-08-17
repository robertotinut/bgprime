<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create a new order for a user and product.
     *
     * @throws Exception
     */
    public function createOrder(User $user, Product $product): Order
    {
        if (!$product->is_active) {
            throw new Exception("Produk {$product->name} saat ini sedang tidak aktif.");
        }

        if ($product->stock_qty <= 0) {
            throw new Exception("Stok untuk produk {$product->name} saat ini sedang habis.");
        }

        return DB::transaction(function () use ($user, $product) {
            $invoiceNumber = $this->generateInvoiceNumber();

            return Order::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => $user->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_price' => $product->price,
                'amount' => $product->price,
                'payment_status' => Order::PAYMENT_PENDING,
                'order_status' => Order::ORDER_WAITING_PAYMENT,
                'fulfillment_status' => Order::FULFILLMENT_PENDING,
            ]);
        });
    }

    /**
     * Cancel an order if it is still in pending status.
     *
     * @throws Exception
     */
    public function cancelOrder(Order $order): Order
    {
        if ($order->payment_status !== Order::PAYMENT_PENDING) {
            throw new Exception("Pesanan dengan status pembayaran {$order->payment_status} tidak dapat dibatalkan secara otomatis.");
        }

        $order->update([
            'order_status' => Order::ORDER_CANCELLED,
        ]);

        return $order->fresh();
    }

    /**
     * Generate unique sequential invoice number in format INV-YYYYMMDD-XXXXX.
     */
    public function generateInvoiceNumber(): string
    {
        $datePrefix = Carbon::now()->format('Ymd');
        $prefix = "INV-{$datePrefix}-";

        $lastOrder = Order::where('invoice_number', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastOrder && preg_match('/INV-\d{8}-(\d{5})/', $lastOrder->invoice_number, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
