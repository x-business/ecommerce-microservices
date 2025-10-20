<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CatalogServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed test products
        Product::factory()->count(5)->create();
    }

    /**
     * Test getting all products
     */
    public function test_can_get_all_products(): void
    {
        $response = $this->getJson('/api/catalog/products');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'name',
                                'description',
                                'price',
                                'sku',
                                'stock_quantity',
                                'image_url',
                                'category',
                                'is_active',
                                'created_at',
                                'updated_at'
                            ]
                        ],
                        'current_page',
                        'last_page',
                        'per_page',
                        'total'
                    ],
                    'message'
                ])
                ->assertJson(['success' => true]);
    }

    /**
     * Test getting products with pagination
     */
    public function test_can_get_products_with_pagination(): void
    {
        $response = $this->getJson('/api/catalog/products?per_page=2&page=1');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data',
                        'current_page',
                        'last_page',
                        'per_page',
                        'total'
                    ]
                ]);

        $data = $response->json('data');
        $this->assertCount(2, $data['data']);
        $this->assertEquals(1, $data['current_page']);
    }

    /**
     * Test filtering products by category
     */
    public function test_can_filter_products_by_category(): void
    {
        // Create products with specific categories
        Product::factory()->create(['category' => 'Electronics']);
        Product::factory()->create(['category' => 'Clothing']);

        $response = $this->getJson('/api/catalog/products?category=Electronics');

        $response->assertStatus(200);
        
        $products = $response->json('data.data');
        foreach ($products as $product) {
            $this->assertEquals('Electronics', $product['category']);
        }
    }

    /**
     * Test searching products by name
     */
    public function test_can_search_products_by_name(): void
    {
        Product::factory()->create(['name' => 'Wireless Headphones']);
        Product::factory()->create(['name' => 'Gaming Mouse']);

        $response = $this->getJson('/api/catalog/products?search=Headphones');

        $response->assertStatus(200);
        
        $products = $response->json('data.data');
        $this->assertCount(1, $products);
        $this->assertEquals('Wireless Headphones', $products[0]['name']);
    }

    /**
     * Test filtering products by price range
     */
    public function test_can_filter_products_by_price_range(): void
    {
        Product::factory()->create(['price' => 50.00]);
        Product::factory()->create(['price' => 150.00]);
        Product::factory()->create(['price' => 250.00]);

        $response = $this->getJson('/api/catalog/products?min_price=100&max_price=200');

        $response->assertStatus(200);
        
        $products = $response->json('data.data');
        foreach ($products as $product) {
            $this->assertGreaterThanOrEqual(100, $product['price']);
            $this->assertLessThanOrEqual(200, $product['price']);
        }
    }

    /**
     * Test getting a single product
     */
    public function test_can_get_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/catalog/products/{$product->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'description',
                        'price',
                        'sku',
                        'stock_quantity',
                        'image_url',
                        'category',
                        'is_active'
                    ],
                    'message'
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $product->id,
                        'name' => $product->name
                    ]
                ]);
    }

    /**
     * Test getting non-existent product
     */
    public function test_cannot_get_non_existent_product(): void
    {
        $response = $this->getJson('/api/catalog/products/999999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Product not found'
                ]);
    }

    /**
     * Test getting inactive product
     */
    public function test_cannot_get_inactive_product(): void
    {
        $product = Product::factory()->create(['is_active' => false]);

        $response = $this->getJson("/api/catalog/products/{$product->id}");

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Product not found'
                ]);
    }

    /**
     * Test getting product categories
     */
    public function test_can_get_product_categories(): void
    {
        Product::factory()->create(['category' => 'Electronics']);
        Product::factory()->create(['category' => 'Clothing']);
        Product::factory()->create(['category' => 'Electronics']); // Duplicate

        $response = $this->getJson('/api/catalog/categories');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ])
                ->assertJson(['success' => true]);

        $categories = $response->json('data');
        $this->assertContains('Electronics', $categories);
        $this->assertContains('Clothing', $categories);
        $this->assertCount(2, $categories); // Should not have duplicates
    }
}
