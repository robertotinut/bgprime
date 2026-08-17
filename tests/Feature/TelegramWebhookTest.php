<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelegramWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create([
            'name' => 'Productivity',
            'slug' => 'productivity',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Notion Plus',
            'slug' => 'notion-plus',
            'duration_label' => '1 Bulan',
            'price' => 25000,
            'stock_qty' => 5,
            'is_active' => true,
        ]);
    }

    public function test_transaction_webhook_start_command_creates_user(): void
    {
        $payload = [
            'update_id' => 10001,
            'message' => [
                'message_id' => 1,
                'from' => [
                    'id' => 5551234,
                    'is_bot' => false,
                    'first_name' => 'Bob',
                    'last_name' => 'Marley',
                    'username' => 'bobmarley',
                ],
                'chat' => [
                    'id' => 5551234,
                    'type' => 'private',
                ],
                'date' => time(),
                'text' => '/start',
            ],
        ];

        $response = $this->postJson('/api/telegram/transaction/webhook', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'telegram_id' => 5551234,
            'telegram_username' => 'bobmarley',
            'first_name' => 'Bob',
        ]);
    }

    public function test_transaction_webhook_handles_checkout_callback(): void
    {
        $user = User::create([
            'name' => 'Charlie',
            'telegram_id' => 7778889,
            'telegram_username' => 'charlie',
        ]);

        $payload = [
            'update_id' => 10002,
            'callback_query' => [
                'id' => 'cb_123',
                'from' => [
                    'id' => 7778889,
                    'is_bot' => false,
                    'first_name' => 'Charlie',
                ],
                'message' => [
                    'message_id' => 20,
                    'chat' => [
                        'id' => 7778889,
                    ],
                ],
                'data' => "create_order:{$this->product->id}",
            ],
        ];

        $response = $this->postJson('/api/telegram/transaction/webhook', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'product_id' => $this->product->id,
            'payment_status' => Order::PAYMENT_PENDING,
            'order_status' => Order::ORDER_WAITING_PAYMENT,
        ]);
    }

    public function test_delivery_webhook_start_updates_delivery_started_at(): void
    {
        $payload = [
            'update_id' => 10003,
            'message' => [
                'message_id' => 2,
                'from' => [
                    'id' => 99911122,
                    'is_bot' => false,
                    'first_name' => 'Dave',
                    'username' => 'dave_user',
                ],
                'chat' => [
                    'id' => 99911122,
                    'type' => 'private',
                ],
                'date' => time(),
                'text' => '/start activate',
            ],
        ];

        $response = $this->postJson('/api/telegram/delivery/webhook', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'telegram_id' => 99911122,
            'telegram_username' => 'dave_user',
        ]);
    }
}
