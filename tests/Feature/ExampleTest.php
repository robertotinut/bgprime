<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test storefront renders successfully with clean dark UI.
     */
    public function test_storefront_renders_successfully(): void
    {
        $category = Category::create([
            'name' => 'AI Tools',
            'slug' => 'ai-tools',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'ChatGPT Plus',
            'slug' => 'chatgpt-plus',
            'duration_label' => '30 Hari',
            'fulfillment_type' => Product::FULFILLMENT_INSTANT,
            'price' => 45000,
            'stock_qty' => 5,
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('BGPrime');
        $response->assertSee('ChatGPT Plus');
    }

    /**
     * Test /login redirects to /admin/login.
     */
    public function test_login_redirects_to_admin_login(): void
    {
        $response = $this->get('/login');

        $response->assertRedirect('/admin/login');
    }

    /**
     * Test order tracking endpoint.
     */
    public function test_order_tracking_endpoint(): void
    {
        $user = User::create([
            'name' => 'Customer Test',
            'telegram_id' => 12345,
            'telegram_username' => 'testuser',
        ]);

        $category = Category::create([
            'name' => 'Design',
            'slug' => 'design',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Canva Pro',
            'slug' => 'canva-pro',
            'duration_label' => '30 Hari',
            'fulfillment_type' => Product::FULFILLMENT_MANUAL,
            'price' => 20000,
            'stock_qty' => 3,
            'is_active' => true,
        ]);

        $order = Order::create([
            'invoice_number' => 'INV-20260817-99999',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 20000,
            'amount' => 20000,
            'order_status' => Order::ORDER_WAITING_PAYMENT,
            'payment_status' => Order::PAYMENT_PENDING,
            'fulfillment_status' => Order::FULFILLMENT_PENDING,
        ]);

        $response = $this->postJson('/api/order/track', [
            'invoice' => 'INV-20260817-99999',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.invoice_number', 'INV-20260817-99999');
        $response->assertJsonPath('data.product_name', 'Canva Pro');
    }
}
