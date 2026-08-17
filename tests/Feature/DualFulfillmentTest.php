<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCredential;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\Telegram\AdminNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class DualFulfillmentTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;
    protected OrderService $orderService;
    protected User $user;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = app(PaymentService::class);
        $this->orderService = app(OrderService::class);

        $this->user = User::create([
            'name' => 'Test Customer',
            'telegram_id' => 12345678,
            'telegram_username' => 'customer_tg',
        ]);

        $this->category = Category::create([
            'name' => 'AI Tools',
            'slug' => 'ai-tools',
            'is_active' => true,
        ]);
    }

    public function test_instant_product_auto_delivers_from_pool_on_payment_approval(): void
    {
        $instantProduct = Product::create([
            'category_id' => $this->category->id,
            'name' => 'ChatGPT Instant',
            'slug' => 'chatgpt-instant',
            'duration_label' => '30 Hari',
            'fulfillment_type' => Product::FULFILLMENT_INSTANT,
            'price' => 45000,
            'stock_qty' => 2,
            'is_active' => true,
        ]);

        // Preload 2 credentials into pool
        $cred1 = ProductCredential::create([
            'product_id' => $instantProduct->id,
            'username' => 'instant1@gmail.com',
            'password' => 'pass123',
            'notes' => 'Catatan 1',
            'is_used' => false,
        ]);

        $cred2 = ProductCredential::create([
            'product_id' => $instantProduct->id,
            'username' => 'instant2@gmail.com',
            'password' => 'pass456',
            'notes' => 'Catatan 2',
            'is_used' => false,
        ]);

        $order = $this->orderService->createOrder($this->user, $instantProduct);
        $this->paymentService->confirmPayment($order);

        // Approve payment
        $approvedOrder = $this->paymentService->approvePayment($order);

        // Order should be automatically completed & fulfillment sent
        $this->assertEquals(Order::ORDER_COMPLETED, $approvedOrder->order_status);
        $this->assertEquals(Order::FULFILLMENT_SENT, $approvedOrder->fulfillment_status);

        // First credential should now be used
        $this->assertTrue($cred1->fresh()->is_used);
        $this->assertEquals($order->id, $cred1->fresh()->order_id);

        // Second credential remains unused
        $this->assertFalse($cred2->fresh()->is_used);

        // Stock decreased to 1
        $this->assertEquals(1, $instantProduct->fresh()->stock_qty);
    }

    public function test_manual_product_stays_waiting_fulfillment_on_payment_approval(): void
    {
        $manualProduct = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Netflix Reseller On Demand',
            'slug' => 'netflix-reseller',
            'duration_label' => '30 Hari',
            'fulfillment_type' => Product::FULFILLMENT_MANUAL,
            'price' => 35000,
            'stock_qty' => 5,
            'is_active' => true,
        ]);

        $order = $this->orderService->createOrder($this->user, $manualProduct);
        $this->paymentService->confirmPayment($order);

        $approvedOrder = $this->paymentService->approvePayment($order);

        // For manual reseller products, order is in processing and waiting for admin fulfillment
        $this->assertEquals(Order::ORDER_PROCESSING, $approvedOrder->order_status);
        $this->assertEquals(Order::FULFILLMENT_WAITING, $approvedOrder->fulfillment_status);
        $this->assertEquals(4, $manualProduct->fresh()->stock_qty);
    }

    public function test_payment_confirmation_calls_admin_notification_service(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Canva Pro',
            'slug' => 'canva-pro',
            'duration_label' => '30 Hari',
            'fulfillment_type' => Product::FULFILLMENT_MANUAL,
            'price' => 20000,
            'stock_qty' => 5,
            'is_active' => true,
        ]);

        $order = $this->orderService->createOrder($this->user, $product);

        $mockAdminNotification = Mockery::mock(AdminNotificationService::class);
        $mockAdminNotification->shouldReceive('notifyPaymentConfirmation')
            ->once()
            ->with(Mockery::on(fn ($o) => $o->id === $order->id))
            ->andReturn(true);

        $paymentService = new PaymentService(
            stockService: app(\App\Services\StockService::class),
            fulfillmentService: app(\App\Services\FulfillmentService::class),
            adminNotificationService: $mockAdminNotification
        );

        $paymentService->confirmPayment($order);
    }
}
