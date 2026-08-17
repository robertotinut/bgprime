<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StockService $stockService;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = app(StockService::class);

        $category = Category::create([
            'name' => 'Design',
            'slug' => 'design',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Canva Pro',
            'slug' => 'canva-pro',
            'duration_label' => '30 Hari',
            'price' => 20000,
            'stock_qty' => 5,
            'low_stock_threshold' => 2,
            'is_active' => true,
        ]);
    }

    public function test_can_add_stock_and_record_movement(): void
    {
        $updatedProduct = $this->stockService->adjustStock(
            product: $this->product,
            quantity: 10,
            type: StockMovement::TYPE_MANUAL_ADD,
            notes: 'Restock supplier A'
        );

        $this->assertEquals(15, $updatedProduct->stock_qty);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => StockMovement::TYPE_MANUAL_ADD,
            'quantity' => 10,
            'before_qty' => 5,
            'after_qty' => 15,
            'notes' => 'Restock supplier A',
        ]);
    }

    public function test_cannot_reduce_stock_below_zero(): void
    {
        $this->expectException(Exception::class);

        $this->stockService->adjustStock(
            product: $this->product,
            quantity: 10,
            type: StockMovement::TYPE_MANUAL_REDUCE,
            notes: 'Reduce too much'
        );
    }

    public function test_can_set_direct_stock_adjustment(): void
    {
        $updatedProduct = $this->stockService->adjustStock(
            product: $this->product,
            quantity: 3,
            type: StockMovement::TYPE_ADJUSTMENT,
            notes: 'Opname fisik'
        );

        $this->assertEquals(3, $updatedProduct->stock_qty);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => StockMovement::TYPE_ADJUSTMENT,
            'before_qty' => 5,
            'after_qty' => 3,
        ]);
    }
}
