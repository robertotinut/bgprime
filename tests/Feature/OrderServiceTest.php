<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;
    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = app(OrderService::class);

        $this->user = User::create([
            'name' => 'John Doe',
            'telegram_id' => 12345678,
            'telegram_username' => 'johndoe',
        ]);

        $category = Category::create([
            'name' => 'AI Tools',
            'slug' => 'ai-tools',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'ChatGPT Premium',
            'slug' => 'chatgpt-premium',
            'duration_label' => '30 Hari',
            'price' => 45000,
            'stock_qty' => 3,
            'is_active' => true,
        ]);
    }

    public function test_can_create_order_with_valid_invoice_format(): void
    {
        $order = $this->orderService->createOrder($this->user, $this->product);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertMatchesRegularExpression('/^INV-\d{8}-\d{5}$/', $order->invoice_number);
        $this->assertEquals('ChatGPT Premium', $order->product_name);
        $this->assertEquals(45000, $order->product_price);
        $this->assertEquals(45000, $order->amount);
        $this->assertEquals(Order::PAYMENT_PENDING, $order->payment_status);
        $this->assertEquals(Order::ORDER_WAITING_PAYMENT, $order->order_status);

        // Product stock must NOT decrease at creation time (PRD rule)
        $this->assertEquals(3, $this->product->fresh()->stock_qty);
    }

    public function test_cannot_order_out_of_stock_product(): void
    {
        $this->product->update(['stock_qty' => 0]);

        $this->expectException(Exception::class);
        $this->orderService->createOrder($this->user, $this->product);
    }

    public function test_can_cancel_pending_order(): void
    {
        $order = $this->orderService->createOrder($this->user, $this->product);
        $cancelledOrder = $this->orderService->cancelOrder($order);

        $this->assertEquals(Order::ORDER_CANCELLED, $cancelledOrder->order_status);
    }
}
