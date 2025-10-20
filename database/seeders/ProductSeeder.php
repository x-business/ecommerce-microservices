<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Wireless Bluetooth Headphones',
                'description' => 'High-quality wireless headphones with noise cancellation and 30-hour battery life.',
                'price' => 199.99,
                'sku' => 'WBH-001',
                'stock_quantity' => 50,
                'image_url' => 'https://via.placeholder.com/300x300?text=Headphones',
                'category' => 'Electronics',
                'is_active' => true,
            ],
            [
                'name' => 'Smart Fitness Watch',
                'description' => 'Advanced fitness tracking watch with heart rate monitor and GPS.',
                'price' => 299.99,
                'sku' => 'SFW-002',
                'stock_quantity' => 30,
                'image_url' => 'https://via.placeholder.com/300x300?text=Smart+Watch',
                'category' => 'Electronics',
                'is_active' => true,
            ],
            [
                'name' => 'Organic Cotton T-Shirt',
                'description' => 'Comfortable organic cotton t-shirt, available in multiple colors.',
                'price' => 29.99,
                'sku' => 'OCT-003',
                'stock_quantity' => 100,
                'image_url' => 'https://via.placeholder.com/300x300?text=T-Shirt',
                'category' => 'Clothing',
                'is_active' => true,
            ],
            [
                'name' => 'Stainless Steel Water Bottle',
                'description' => 'Insulated stainless steel water bottle that keeps drinks cold for 24 hours.',
                'price' => 39.99,
                'sku' => 'SSB-004',
                'stock_quantity' => 75,
                'image_url' => 'https://via.placeholder.com/300x300?text=Water+Bottle',
                'category' => 'Accessories',
                'is_active' => true,
            ],
            [
                'name' => 'Wireless Phone Charger',
                'description' => 'Fast wireless charging pad compatible with all Qi-enabled devices.',
                'price' => 49.99,
                'sku' => 'WPC-005',
                'stock_quantity' => 40,
                'image_url' => 'https://via.placeholder.com/300x300?text=Charger',
                'category' => 'Electronics',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}