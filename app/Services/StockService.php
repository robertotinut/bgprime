<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Adjust product stock with row locking and audit logging.
     *
     * @throws Exception
     */
    public function adjustStock(
        Product|int $product,
        int $quantity,
        string $type,
        ?string $notes = null,
        ?int $createdBy = null,
        ?int $orderId = null
    ): Product {
        $productId = $product instanceof Product ? $product->id : $product;

        return DB::transaction(function () use ($productId, $quantity, $type, $notes, $createdBy, $orderId) {
            /** @var Product $lockedProduct */
            $lockedProduct = Product::lockForUpdate()->findOrFail($productId);
            $beforeQty = (int) $lockedProduct->stock_qty;

            $afterQty = match ($type) {
                StockMovement::TYPE_MANUAL_ADD, StockMovement::TYPE_REFUND => $beforeQty + abs($quantity),
                StockMovement::TYPE_MANUAL_REDUCE, StockMovement::TYPE_SALE => $beforeQty - abs($quantity),
                StockMovement::TYPE_ADJUSTMENT => $quantity,
                default => throw new Exception("Tipe pergerakan stok tidak valid: {$type}"),
            };

            if ($afterQty < 0) {
                throw new Exception("Stok produk {$lockedProduct->name} tidak mencukupi (sisa: {$beforeQty}, diminta: {$quantity})");
            }

            $lockedProduct->update([
                'stock_qty' => $afterQty,
            ]);

            StockMovement::create([
                'product_id' => $lockedProduct->id,
                'order_id' => $orderId,
                'type' => $type,
                'quantity' => abs($quantity),
                'before_qty' => $beforeQty,
                'after_qty' => $afterQty,
                'notes' => $notes,
                'created_by' => $createdBy,
                'created_at' => now(),
            ]);

            return $lockedProduct->fresh();
        });
    }

    /**
     * Decrement stock for an order atomically and idempotently.
     *
     * @throws Exception
     */
    public function reduceStockForOrder(Order $order): void
    {
        // Check idempotency: if movement for this order already exists for sale, skip
        $alreadyReduced = StockMovement::where('order_id', $order->id)
            ->where('type', StockMovement::TYPE_SALE)
            ->exists();

        if ($alreadyReduced) {
            return;
        }

        $this->adjustStock(
            product: $order->product_id,
            quantity: 1,
            type: StockMovement::TYPE_SALE,
            notes: "Pengurangan stok otomatis untuk order {$order->invoice_number}",
            createdBy: null,
            orderId: $order->id
        );
    }

    /**
     * Restore stock for a refunded/cancelled order.
     *
     * @throws Exception
     */
    public function restoreStockForOrder(Order $order, string $reason = 'Order refund'): void
    {
        $hasSale = StockMovement::where('order_id', $order->id)
            ->where('type', StockMovement::TYPE_SALE)
            ->exists();

        $alreadyRefunded = StockMovement::where('order_id', $order->id)
            ->where('type', StockMovement::TYPE_REFUND)
            ->exists();

        if (!$hasSale || $alreadyRefunded) {
            return;
        }

        $this->adjustStock(
            product: $order->product_id,
            quantity: 1,
            type: StockMovement::TYPE_REFUND,
            notes: "Pengembalian stok untuk {$order->invoice_number}: {$reason}",
            createdBy: auth()->id() ?? null,
            orderId: $order->id
        );
    }
}
