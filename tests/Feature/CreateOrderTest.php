<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Jobs\ExportOrderJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_order_creation_decrements_stock_and_calculates_total(): void
    {
        Http::fake();
        Queue::fake();

        $customer = Customer::factory()->create();
        $product = Product::factory()->create([
            'price'          => 1000.00,
            'stock_quantity' => 10,
        ]);

        $payload = [
            'customer_id' => $customer->id,
            'items'       => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'status',
                'total_amount',
                'customer' => ['id', 'name', 'email'],
                'items'    => [['id', 'product_id', 'quantity', 'unit_price', 'total_price']],
            ])
            ->assertJsonPath('status', OrderStatus::New->value)
            ->assertJsonPath('total_amount', 3000.0);

        $this->assertDatabaseHas('orders', [
            'customer_id'  => $customer->id,
            'status'       => OrderStatus::New->value,
            'total_amount' => '3000.00',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id'  => $product->id,
            'quantity'    => 3,
            'unit_price'  => '1000.00',
            'total_price' => '3000.00',
        ]);

        $product->refresh();
        $this->assertEquals(7, $product->stock_quantity);
    }

    public function test_order_creation_fails_when_stock_insufficient(): void
    {
        Queue::fake();

        $customer = Customer::factory()->create();
        $product = Product::factory()->create([
            'price'          => 500.00,
            'stock_quantity' => 2,
        ]);

        $response = $this->postJson('/api/v1/orders', [
            'customer_id' => $customer->id,
            'items'       => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message']);

        $product->refresh();
        $this->assertEquals(2, $product->stock_quantity);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_creation_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id', 'items']);
    }

    public function test_order_creation_fails_with_nonexistent_customer(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $response = $this->postJson('/api/v1/orders', [
            'customer_id' => 999999,
            'items'       => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id']);
    }

    public function test_order_creation_fails_with_nonexistent_product(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->postJson('/api/v1/orders', [
            'customer_id' => $customer->id,
            'items'       => [['product_id' => 999999, 'quantity' => 1]],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_id']);
    }

    public function test_status_change_to_confirmed_dispatches_export_job(): void
    {
        Http::fake();
        Queue::fake();

        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $order = Order::create([
            'customer_id'  => $customer->id,
            'status'       => OrderStatus::New,
            'total_amount' => 500.00,
        ]);
        $order->items()->create([
            'product_id'  => $product->id,
            'quantity'    => 1,
            'unit_price'  => 500.00,
            'total_price' => 500.00,
        ]);

        $response = $this->patchJson("/api/v1/orders/{$order->id}/status", [
            'status' => OrderStatus::Confirmed->value,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', OrderStatus::Confirmed->value);

        $order->refresh();
        $this->assertNotNull($order->confirmed_at);

        Queue::assertPushedOn('exports', ExportOrderJob::class);
    }

    public function test_invalid_status_transition_returns_422(): void
    {
        $customer = Customer::factory()->create();
        $order = Order::create([
            'customer_id'  => $customer->id,
            'status'       => OrderStatus::New,
            'total_amount' => 100.00,
        ]);

        $response = $this->patchJson("/api/v1/orders/{$order->id}/status", [
            'status' => OrderStatus::Shipped->value,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message']);
    }

    public function test_create_order_with_multiple_items(): void
    {
        Http::fake();
        Queue::fake();

        $customer = Customer::factory()->create();
        $product1 = Product::factory()->create(['price' => 200.00, 'stock_quantity' => 10]);
        $product2 = Product::factory()->create(['price' => 350.00, 'stock_quantity' => 5]);

        $response = $this->postJson('/api/v1/orders', [
            'customer_id' => $customer->id,
            'items'       => [
                ['product_id' => $product1->id, 'quantity' => 2],
                ['product_id' => $product2->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('total_amount', 750.0);

        $this->assertDatabaseCount('order_items', 2);
    }

    public function test_products_list_returns_paginated_data(): void
    {
        Product::factory(20)->create();

        $response = $this->getJson('/api/v1/products?per_page=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'name', 'sku', 'price', 'stock_quantity', 'category']],
                'links',
                'meta',
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    public function test_products_can_be_filtered_by_category(): void
    {
        Product::factory(5)->create(['category' => 'Двигатель']);
        Product::factory(3)->create(['category' => 'Кузов']);

        $response = $this->getJson('/api/v1/products?category=Двигатель');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    public function test_orders_list_without_n_plus_one(): void
    {
        Http::fake();
        Queue::fake();

        $customer = Customer::factory()->create();
        $products = Product::factory(3)->create(['stock_quantity' => 10]);

        foreach (range(1, 3) as $i) {
            $order = Order::create([
                'customer_id'  => $customer->id,
                'status'       => OrderStatus::New,
                'total_amount' => 100.00,
            ]);
            $order->items()->create([
                'product_id'  => $products->first()->id,
                'quantity'    => 1,
                'unit_price'  => 100.00,
                'total_price' => 100.00,
            ]);
        }

        $response = $this->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'status', 'customer', 'items']],
            ]);
    }
}
