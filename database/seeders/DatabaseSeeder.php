<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin User
        User::updateOrCreate(
            ['email' => 'admin@bgprime.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Categories
        $categories = [
            [
                'name' => 'Design',
                'slug' => 'design',
                'icon' => '🎨',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'AI Tools',
                'slug' => 'ai-tools',
                'icon' => '🤖',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Streaming',
                'slug' => 'streaming',
                'icon' => '📺',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Music',
                'slug' => 'music',
                'icon' => '🎵',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Productivity',
                'slug' => 'productivity',
                'icon' => '☁️',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        $catMap = [];
        foreach ($categories as $catData) {
            $cat = Category::updateOrCreate(['slug' => $catData['slug']], $catData);
            $catMap[$cat->slug] = $cat->id;
        }

        // 3. Products
        $products = [
            [
                'category_id' => $catMap['design'],
                'name' => 'Canva Pro 30 Hari',
                'slug' => 'canva-pro-30-hari',
                'description' => 'Akses Canva Pro private/invite selama 30 hari penuh. Garansi aktif.',
                'duration_label' => '30 Hari',
                'price' => 20000,
                'stock_qty' => 5,
                'low_stock_threshold' => 2,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $catMap['design'],
                'name' => 'CapCut Pro 30 Hari',
                'slug' => 'capcut-pro-30-hari',
                'description' => 'Akses CapCut Pro desktop & mobile selama 30 hari.',
                'duration_label' => '30 Hari',
                'price' => 25000,
                'stock_qty' => 7,
                'low_stock_threshold' => 2,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'category_id' => $catMap['ai-tools'],
                'name' => 'ChatGPT Plus / Premium',
                'slug' => 'chatgpt-premium-30-hari',
                'description' => 'Akses GPT-4o, DALL-E, Advanced Data Analysis selama 30 hari.',
                'duration_label' => '30 Hari',
                'price' => 45000,
                'stock_qty' => 3,
                'low_stock_threshold' => 2,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'category_id' => $catMap['ai-tools'],
                'name' => 'Claude Pro 30 Hari',
                'slug' => 'claude-pro-30-hari',
                'description' => 'Akses Claude 3.5 Sonnet / Opus dengan limit tinggi.',
                'duration_label' => '30 Hari',
                'price' => 40000,
                'stock_qty' => 2,
                'low_stock_threshold' => 2,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'category_id' => $catMap['streaming'],
                'name' => 'Netflix Premium 1 Profil',
                'slug' => 'netflix-premium-30-hari',
                'description' => '1 Profil Private Ultra HD 4K garansi 30 hari.',
                'duration_label' => '30 Hari',
                'price' => 35000,
                'stock_qty' => 6,
                'low_stock_threshold' => 2,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'category_id' => $catMap['music'],
                'name' => 'Spotify Individual 1 Bulan',
                'slug' => 'spotify-individual-1-bulan',
                'description' => 'Akun Spotify Premium bebas iklan, download lagu.',
                'duration_label' => '30 Hari',
                'price' => 15000,
                'stock_qty' => 8,
                'low_stock_threshold' => 2,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($products as $prodData) {
            Product::updateOrCreate(['slug' => $prodData['slug']], $prodData);
        }
    }
}
