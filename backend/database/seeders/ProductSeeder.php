<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Inventory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Produk Kaos
        $kaos = Product::create([
            'name' => 'Kaos Oversize Cotton Combed',
            'slug' => 'kaos-oversize-cotton-combed',
            'sku' => 'KOS-001',
            'description' => '<p>Kaos oversize dengan bahan cotton combed 24s yang nyaman dan berkualitas tinggi. Cocok untuk pemakaian sehari-hari dengan desain yang simple dan elegan.</p>',
            'short_description' => 'Kaos oversize cotton combed 24s, nyaman untuk daily wear',
            'price' => 120000,
            'compare_price' => 150000,
            'weight' => 200, // gram
            'dimensions' => '70x50x2', // PxLxT cm
            'status' => 'active',
            'is_featured' => true,
            'meta_data' => [
                'seo' => [
                    'title' => 'Kaos Oversize Cotton Combed - Premium Quality',
                    'description' => 'Beli kaos oversize cotton combed berkualitas tinggi. Nyaman, stylish, dan terjangkau.',
                    'keywords' => 'kaos oversize, cotton combed, fashion pria'
                ],
                'specs' => [
                    'bahan' => 'Cotton Combed 24s',
                    'fit' => 'Oversize',
                    'care' => 'Machine wash cold'
                ]
            ]
        ]);

        // Attach categories (Fashion > Pakaian Pria > Kaos)
        $kaos->categories()->attach([1, 4, 7]); // Fashion, Pakaian Pria, Kaos

        // Product Images
        ProductImage::create([
            'product_id' => $kaos->id,
            'path' => '/images/products/kaos-oversize-1.jpg',
            'alt_text' => 'Kaos Oversize Cotton Combed - Tampak Depan',
            'sort_order' => 1,
            'is_primary' => true,
        ]);

        ProductImage::create([
            'product_id' => $kaos->id,
            'path' => '/images/products/kaos-oversize-2.jpg',
            'alt_text' => 'Kaos Oversize Cotton Combed - Tampak Belakang',
            'sort_order' => 2,
        ]);

        // Product Variants (Size)
        $sizes = ['S', 'M', 'L', 'XL'];
        foreach ($sizes as $index => $size) {
            $variant = ProductVariant::create([
                'product_id' => $kaos->id,
                'name' => $size,
                'sku' => 'KOS-001-' . $size,
                'price_adjustment' => $size === 'XL' ? 10000 : 0, // XL +10k
                'sort_order' => $index + 1,
            ]);

            // Inventory untuk setiap variant
            Inventory::create([
                'product_id' => $kaos->id,
                'product_variant_id' => $variant->id,
                'quantity' => 50,
                'reserved_quantity' => 0,
                'minimum_stock' => 5,
            ]);
        }

        // Produk Smartphone
        $phone = Product::create([
            'name' => 'Smartphone Android Pro Max',
            'slug' => 'smartphone-android-pro-max',
            'sku' => 'PHN-001',
            'description' => '<p>Smartphone flagship dengan performa tinggi, kamera canggih, dan baterai tahan lama. Cocok untuk gaming, fotografi, dan produktivitas.</p>',
            'short_description' => 'Smartphone flagship dengan performa tinggi dan kamera canggih',
            'price' => 8500000,
            'compare_price' => 9500000,
            'weight' => 200,
            'dimensions' => '16x8x1',
            'status' => 'active',
            'is_featured' => true,
            'meta_data' => [
                'seo' => [
                    'title' => 'Smartphone Android Pro Max - Flagship Performance',
                    'description' => 'Smartphone flagship terbaru dengan performa tinggi, kamera pro, dan fitur canggih.',
                    'keywords' => 'smartphone, android, flagship, kamera pro'
                ],
                'specs' => [
                    'processor' => 'Snapdragon 8 Gen 2',
                    'ram' => '12GB',
                    'storage' => '256GB',
                    'camera' => '108MP Triple Camera',
                    'battery' => '5000mAh',
                    'os' => 'Android 14'
                ]
            ]
        ]);

        $phone->categories()->attach([2, 10]); // Elektronik, Smartphone

        ProductImage::create([
            'product_id' => $phone->id,
            'path' => '/images/products/phone-pro-1.jpg',
            'alt_text' => 'Smartphone Android Pro Max - Tampak Depan',
            'sort_order' => 1,
            'is_primary' => true,
        ]);

        // Variants untuk warna
        $colors = [
            ['name' => 'Midnight Black', 'adjustment' => 0],
            ['name' => 'Ocean Blue', 'adjustment' => 0],
            ['name' => 'Rose Gold', 'adjustment' => 200000],
        ];

        foreach ($colors as $index => $color) {
            $variant = ProductVariant::create([
                'product_id' => $phone->id,
                'name' => $color['name'],
                'sku' => 'PHN-001-' . strtoupper(str_replace(' ', '', $color['name'])),
                'price_adjustment' => $color['adjustment'],
                'sort_order' => $index + 1,
            ]);

            Inventory::create([
                'product_id' => $phone->id,
                'product_variant_id' => $variant->id,
                'quantity' => 25,
                'reserved_quantity' => 0,
                'minimum_stock' => 3,
            ]);
        }

        // Produk Simple (tanpa variant)
        $sepatu = Product::create([
            'name' => 'Sepatu Sneakers Classic',
            'slug' => 'sepatu-sneakers-classic',
            'sku' => 'SHO-001',
            'description' => '<p>Sepatu sneakers dengan desain classic yang timeless. Cocok untuk berbagai occasion dengan comfort yang maksimal.</p>',
            'short_description' => 'Sepatu sneakers classic yang nyaman dan stylish',
            'price' => 450000,
            'compare_price' => 550000,
            'weight' => 800,
            'dimensions' => '30x12x10',
            'status' => 'active',
            'is_featured' => false,
            'meta_data' => [
                'seo' => [
                    'title' => 'Sepatu Sneakers Classic - Comfort & Style',
                    'description' => 'Sepatu sneakers classic yang nyaman untuk aktivitas sehari-hari.',
                ],
                'specs' => [
                    'material' => 'Canvas & Rubber',
                    'sole' => 'Rubber Outsole',
                    'closure' => 'Lace-up'
                ]
            ]
        ]);

        $sepatu->categories()->attach([1, 6]); // Fashion, Sepatu

        ProductImage::create([
            'product_id' => $sepatu->id,
            'path' => '/images/products/sneakers-1.jpg',
            'alt_text' => 'Sepatu Sneakers Classic',
            'sort_order' => 1,
            'is_primary' => true,
        ]);

        // Inventory tanpa variant
        Inventory::create([
            'product_id' => $sepatu->id,
            'product_variant_id' => null,
            'quantity' => 30,
            'reserved_quantity' => 0,
            'minimum_stock' => 5,
        ]);

        echo "✅ Products seeded successfully!\n";
    }
}
