<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StorefrontController
{
    /**
     * Display the Material Design 3 storefront.
     */
    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('is_active', true)->where('stock_qty', '>', 0);
            }])
            ->orderBy('sort_order')
            ->get();

        $products = Product::where('is_active', true)
            ->where('stock_qty', '>', 0)
            ->with('category')
            ->orderBy('sort_order')
            ->get();

        $totalCompletedOrders = Order::where('order_status', Order::ORDER_COMPLETED)->count();
        $totalProducts = Product::where('is_active', true)->count();
        $totalCategories = Category::where('is_active', true)->count();

        $transactionBotUsername = config('services.telegram.transaction_bot_username', 'transaction_bot');
        $deliveryBotUsername = config('services.telegram.delivery_bot_username', 'delivery_bot');
        $channelUsername = config('services.telegram.channel_username');
        $channelId = config('services.telegram.channel_id');

        return view('storefront', compact(
            'categories',
            'products',
            'totalCompletedOrders',
            'totalProducts',
            'totalCategories',
            'transactionBotUsername',
            'deliveryBotUsername',
            'channelUsername',
            'channelId'
        ));
    }

    /**
     * API to track order status by invoice number.
     */
    public function trackOrder(Request $request): JsonResponse
    {
        $invoice = trim((string) $request->input('invoice', ''));

        if (empty($invoice)) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masukkan nomor invoice.',
            ], 422);
        }

        $order = Order::where('invoice_number', $invoice)
            ->with(['product', 'fulfillment'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan dengan nomor invoice tersebut tidak ditemukan.',
            ], 404);
        }

        $deliveryBotUsername = config('services.telegram.delivery_bot_username', 'delivery_bot');

        return response()->json([
            'success' => true,
            'data' => [
                'invoice_number' => $order->invoice_number,
                'product_name' => $order->product_name,
                'amount_formatted' => $order->formatted_amount,
                'status_label' => $order->customer_status_label,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
                'fulfillment_status' => $order->fulfillment_status,
                'is_instant' => (bool) $order->product?->isInstant(),
                'created_at_formatted' => $order->created_at->format('d M Y, H:i'),
                'paid_at_formatted' => $order->paid_at ? $order->paid_at->format('d M Y, H:i') : null,
                'fulfilled_at_formatted' => $order->fulfilled_at ? $order->fulfilled_at->format('d M Y, H:i') : null,
                'delivery_bot_url' => "https://t.me/{$deliveryBotUsername}?start=activate",
            ],
        ]);
    }
}
