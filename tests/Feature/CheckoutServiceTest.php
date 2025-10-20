<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed test products
        Product::factory()->count(3)->create();
    }

    /**
     * Test creating a new order
     */
    public function test_can_create_order(): void
    {
        $products = Product::all();
        $orderData = [
            'customer_email' => $this->faker->email,
            'customer_name' => $this->faker->name,
            'shipping_address' => $this->faker->address,
            'billing_address' => $this->faker->address,
            'payment_method' => 'credit_card',
            'notes' => 'Test order',
            'items' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity' => 2
                ],
                [
                    'product_id' => $products[1]->id,
                    'quantity' => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/checkout/orders', $orderData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'order_number',
                        'customer_email',
                        'customer_name',
                        'total_amount',
                        'status',
                        'shipping_address',
                        'billing_address',
                        'payment_method',
                        'notes',
                        'order_items' => [
                            '*' => [
                                'id',
                                'product_id',
                                'quantity',
                                'unit_price',
                                'total_price',
                                'product' => [
                                    'id',
                                    'name',
                                    'description',
                                    'price',
                                    'sku'
                                ]
                            ]
                        ]
                    ],
                    'message'
                ])
                ->assertJson(['success' => true]);

        // Verify order was created in database
        $this->assertDatabaseHas('orders', [
            'customer_email' => $orderData['customer_email'],
            'customer_name' => $orderData['customer_name'],
            'status' => 'pending'
        ]);

        // Verify order items were created
        $order = Order::where('customer_email', $orderData['customer_email'])->first();
        $this->assertCount(2, $order->orderItems);
    }

    /**
     * Test order creation with insufficient stock
     */
    public function test_cannot_create_order_with_insufficient_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5]);
        
        $orderData = [
            'customer_email' => $this->faker->email,
            'customer_name' => $this->faker->name,
            'shipping_address' => $this->faker->address,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10 // More than available stock
                ]
            ]
        ];

        $response = $this->postJson('/api/checkout/orders', $orderData);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false
                ])
                ->assertJsonFragment([
                    'message' => "Insufficient stock for product: {$product->name}. Available: 5"
                ]);
    }

    /**
     * Test order creation with inactive product
     */
    public function test_cannot_create_order_with_inactive_product(): void
    {
        $product = Product::factory()->create(['is_active' => false]);
        
        $orderData = [
            'customer_email' => $this->faker->email,
            'customer_name' => $this->faker->name,
            'shipping_address' => $this->faker->address,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/checkout/orders', $orderData);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false
                ])
                ->assertJsonFragment([
                    'message' => "Product with ID {$product->id} not found or inactive"
                ]);
    }

    /**
     * Test order creation validation
     */
    public function test_order_creation_validation(): void
    {
        $response = $this->postJson('/api/checkout/orders', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'customer_email',
                    'customer_name',
                    'shipping_address',
                    'items'
                ]);
    }

    /**
     * Test getting order details
     */
    public function test_can_get_order_details(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->count(2)->create(['order_id' => $order->id]);

        $response = $this->getJson("/api/checkout/orders/{$order->order_number}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'order_number',
                        'customer_email',
                        'customer_name',
                        'total_amount',
                        'status',
                        'order_items' => [
                            '*' => [
                                'id',
                                'product_id',
                                'quantity',
                                'unit_price',
                                'total_price',
                                'product'
                            ]
                        ]
                    ],
                    'message'
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'order_number' => $order->order_number
                    ]
                ]);
    }

    /**
     * Test getting non-existent order
     */
    public function test_cannot_get_non_existent_order(): void
    {
        $response = $this->getJson('/api/checkout/orders/NONEXISTENT');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Order not found'
                ]);
    }

    /**
     * Test updating order status
     */
    public function test_can_update_order_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->patchJson("/api/checkout/orders/{$order->order_number}/status", [
            'status' => 'processing'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'status' => 'processing'
                    ]
                ]);

        // Verify status was updated in database
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing'
        ]);
    }

    /**
     * Test updating order status validation
     */
    public function test_order_status_update_validation(): void
    {
        $order = Order::factory()->create();

        $response = $this->patchJson("/api/checkout/orders/{$order->order_number}/status", [
            'status' => 'invalid_status'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['status']);
    }

    /**
     * Test stock is decremented after order creation
     */
    public function test_stock_is_decremented_after_order(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $originalStock = $product->stock_quantity;
        
        $orderData = [
            'customer_email' => $this->faker->email,
            'customer_name' => $this->faker->name,
            'shipping_address' => $this->faker->address,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3
                ]
            ]
        ];

        $response = $this->postJson('/api/checkout/orders', $orderData);

        $response->assertStatus(201);

        // Verify stock was decremented
        $product->refresh();
        $this->assertEquals($originalStock - 3, $product->stock_quantity);
    }
}
