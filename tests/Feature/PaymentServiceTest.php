<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;
    protected OrderService $orderService;
    protected User $user;
    protected Product $product;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = app(PaymentService::class);
        $this->orderService = app(OrderService::class);

        $this->user = User::create([
            'name' => 'Jane Doe',
            'telegram_id' => 98765432,
            'telegram_username' => 'janedoe',
        ]);

        $category = Category::create([
            'name' => 'Streaming',
            'slug' => 'streaming',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Netflix Premium',
            'slug' => 'netflix-premium',
            'duration_label' => '30 Hari',
            'price' => 35000,
            'stock_qty' => 5,
            'is_active' => true,
        ]);

        $this->order = $this->orderService->createOrder($this->user, $this->product);
    }

    public function test_customer_confirmation_updates_status(): void
    {
        $updatedOrder = $this->paymentService->confirmPayment($this->order);

        $this->assertEquals(Order::PAYMENT_WAITING_CONFIRMATION, $updatedOrder->payment_status);
        // Stock still untouched
        $this->assertEquals(5, $this->product->fresh()->stock_qty);
    }

    public function test_payment_approval_decrements_stock_and_is_idempotent(): void
    {
        $this->paymentService->confirmPayment($this->order);

        // First approval
        $approvedOrder = $this->paymentService->approvePayment($this->order);

        $this->assertEquals(Order::PAYMENT_PAID, $approvedOrder->payment_status);
        $this->assertEquals(Order::ORDER_PROCESSING, $approvedOrder->order_status);
        $this->assertEquals(Order::FULFILLMENT_WAITING, $approvedOrder->fulfillment_status);
        $this->assertNotNull($approvedOrder->paid_at);

        // Stock decreased 5 -> 4
        $this->assertEquals(4, $this->product->fresh()->stock_qty);

        // Check stock movement
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'order_id' => $this->order->id,
            'type' => StockMovement::TYPE_SALE,
            'before_qty' => 5,
            'after_qty' => 4,
        ]);

        // Second approval attempt (Idempotency test)
        $secondApproval = $this->paymentService->approvePayment($approvedOrder);
        $this->assertEquals(4, $this->product->fresh()->stock_qty, 'Stock should NOT decrease a second time.');
    }

    public function test_refund_restores_stock(): void
    {
        $this->paymentService->approvePayment($this->order);
        $this->assertEquals(4, $this->product->fresh()->stock_qty);

        $refundedOrder = $this->paymentService->refundPayment($this->order, 'Customer requested cancellation');

        $this->assertEquals(Order::PAYMENT_REFUNDED, $refundedOrder->payment_status);
        $this->assertEquals(5, $this->product->fresh()->stock_qty);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'order_id' => $this->order->id,
            'type' => StockMovement::TYPE_REFUND,
            'before_qty' => 4,
            'after_qty' => 5,
        ]);
    }
}
