<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\Product;
use App\Models\User;
use App\Services\FulfillmentService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FulfillmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FulfillmentService $fulfillmentService;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fulfillmentService = app(FulfillmentService::class);
        $orderService = app(OrderService::class);
        $paymentService = app(PaymentService::class);

        $user = User::create([
            'name' => 'Alice',
            'telegram_id' => 11223344,
            'telegram_username' => 'alice_tg',
        ]);

        $category = Category::create([
            'name' => 'Music',
            'slug' => 'music',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Spotify Premium',
            'slug' => 'spotify-premium',
            'duration_label' => '30 Hari',
            'price' => 15000,
            'stock_qty' => 5,
            'is_active' => true,
        ]);

        $this->order = $orderService->createOrder($user, $product);
        $paymentService->approvePayment($this->order);
    }

    public function test_credentials_are_encrypted_in_database(): void
    {
        $rawUsername = 'alice_spotify@gmail.com';
        $rawPassword = 'SecretPassword123!';

        $fulfillment = $this->fulfillmentService->fulfillOrder(
            $this->order,
            $rawUsername,
            $rawPassword,
            'Jangan ganti password'
        );

        // Through model, decrypted automatically
        $this->assertEquals($rawUsername, $fulfillment->username);
        $this->assertEquals($rawPassword, $fulfillment->password);

        // Directly in database, must be encrypted and not equal to plaintext
        $rawDbRow = DB::table('order_fulfillments')->where('id', $fulfillment->id)->first();
        $this->assertNotEquals($rawUsername, $rawDbRow->username);
        $this->assertNotEquals($rawPassword, $rawDbRow->password);

        // Order is now completed
        $this->assertEquals(Order::ORDER_COMPLETED, $this->order->fresh()->order_status);
        $this->assertEquals(Order::FULFILLMENT_SENT, $this->order->fresh()->fulfillment_status);
    }

    public function test_can_resend_credentials_and_track_count(): void
    {
        $fulfillment = $this->fulfillmentService->fulfillOrder(
            $this->order,
            'user@mail.com',
            'pass123',
            'Notes'
        );

        $this->assertEquals(0, $fulfillment->resend_count);

        $this->fulfillmentService->resendCredential($fulfillment);
        $this->assertEquals(1, $fulfillment->fresh()->resend_count);
        $this->assertNotNull($fulfillment->fresh()->last_resend_at);
    }
}
