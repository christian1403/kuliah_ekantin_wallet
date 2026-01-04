<?php

namespace Database\Factories;

use App\Models\Produk;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Produk>
 */
class ProdukFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Produk::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Define product categories and their typical products with static image URLs
        $categories = [
            'food' => [
                'names' => ['Nasi Ayam', 'Mie Ayam', 'Bakso', 'Soto Ayam', 'Nasi Gudeg', 'Gado-gado', 'Pecel Lele', 'Ayam Geprek', 'Nasi Padang', 'Rawon'],
                'price_range' => [15000, 35000],
                'images' => [
                    'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=400&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=400&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=400&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=400&h=400&fit=crop',
                ]
            ],
            'beverage' => [
                'names' => ['Es Teh', 'Es Jeruk', 'Kopi Hitam', 'Cappuccino', 'Jus Mangga', 'Es Campur', 'Thai Tea', 'Jus Alpukat', 'Lemon Tea', 'Jus Buah Naga'],
                'price_range' => [5000, 20000],
                'images' => [
                    'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=400&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1571091655789-405eb7a3a3a8?w=400&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=400&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1570197788417-0e82375c9371?w=400&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1506802913710-40e2e66339c9?w=400&h=400&fit=crop',
                ]
            ],
            'snack' => [
                'names' => ['Kerupuk', 'Keripik Singkong', 'Pisang Goreng', 'Tahu Isi', 'Risoles', 'Lemper', 'Onde-onde', 'Kue Cucur', 'Martabak Mini', 'Pastel'],
                'price_range' => [3000, 15000],
                'images' => [
                    'https://images.unsplash.com/photo-1599490659213-e2b9527bd087?w=400&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1580013759032-c96505e24572?w=400&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=400&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=400&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1565299585323-38174c4a6c91?w=400&h=400&fit=crop',
                ]
            ],
            'dessert' => [
                'names' => ['Es Krim', 'Pudding', 'Klepon', 'Dadar Gulung', 'Kue Lapis', 'Brownies', 'Donut', 'Churros', 'Panna Cotta', 'Tiramisu'],
                'price_range' => [8000, 25000],
                'images' => [
                    'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=400&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?w=400&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1571115764595-644a1f56a55c?w=400&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=400&h=400&fit=crop',
                ]
            ]
        ];

        $category = $this->faker->randomElement(array_keys($categories));
        $categoryData = $categories[$category];
        $productName = $this->faker->randomElement($categoryData['names']);
        
        return [
            'merchant_id' => function () {
                // Try to get existing merchant, create one if none exists
                return Merchant::inRandomOrder()->first()?->merchant_id ?? 
                       Merchant::factory()->create()->merchant_id;
            },
            'kode_produk' => $this->generateProductCode(),
            'nama' => $productName,
            'kategori' => $category,
            'harga' => $this->faker->numberBetween($categoryData['price_range'][0], $categoryData['price_range'][1]),
            'stok' => $this->faker->numberBetween(0, 100),
            'gambar' => $this->faker->optional(0.8)->randomElement($categoryData['images']),
        ];
    }

    /**
     * Generate a unique product code.
     */
    private function generateProductCode(): string
    {
        do {
            $code = 'PRD' . str_pad($this->faker->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Produk::where('kode_produk', $code)->exists());

        return $code;
    }

    /**
     * Indicate that the product is a food item.
     */
    public function food(): static
    {
        $foodItems = ['Nasi Ayam', 'Mie Ayam', 'Bakso', 'Soto Ayam', 'Nasi Gudeg', 'Gado-gado', 'Pecel Lele', 'Ayam Geprek', 'Nasi Padang', 'Rawon', 'Sate Ayam', 'Rendang', 'Nasi Liwet', 'Gudeg Jogja'];
        
        return $this->state(fn (array $attributes) => [
            'nama' => $this->faker->randomElement($foodItems),
            'kategori' => 'food',
            'harga' => $this->faker->numberBetween(15000, 50000),
            'stok' => $this->faker->numberBetween(10, 50),
        ]);
    }

    /**
     * Indicate that the product is a beverage.
     */
    public function beverage(): static
    {
        $beverages = ['Es Teh', 'Es Jeruk', 'Kopi Hitam', 'Cappuccino', 'Latte', 'Jus Mangga', 'Es Campur', 'Thai Tea', 'Jus Alpukat', 'Lemon Tea', 'Jus Strawberry', 'Smoothie Bowl'];
        
        return $this->state(fn (array $attributes) => [
            'nama' => $this->faker->randomElement($beverages),
            'kategori' => 'beverage',
            'harga' => $this->faker->numberBetween(5000, 25000),
            'stok' => $this->faker->numberBetween(20, 100),
        ]);
    }

    /**
     * Indicate that the product is a snack.
     */
    public function snack(): static
    {
        $snacks = ['Kerupuk', 'Keripik Singkong', 'Pisang Goreng', 'Tahu Isi', 'Risoles', 'Lemper', 'Onde-onde', 'Kue Cucur', 'Martabak Mini', 'Pastel', 'Cilok', 'Siomay'];
        
        return $this->state(fn (array $attributes) => [
            'nama' => $this->faker->randomElement($snacks),
            'kategori' => 'snack',
            'harga' => $this->faker->numberBetween(3000, 18000),
            'stok' => $this->faker->numberBetween(15, 80),
        ]);
    }

    /**
     * Indicate that the product is a dessert.
     */
    public function dessert(): static
    {
        $desserts = ['Es Krim', 'Pudding', 'Klepon', 'Dadar Gulung', 'Kue Lapis', 'Brownies', 'Donut', 'Churros', 'Panna Cotta', 'Tiramisu', 'Cheesecake', 'Red Velvet'];
        
        return $this->state(fn (array $attributes) => [
            'nama' => $this->faker->randomElement($desserts),
            'kategori' => 'dessert',
            'harga' => $this->faker->numberBetween(8000, 30000),
            'stok' => $this->faker->numberBetween(5, 40),
        ]);
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stok' => 0,
        ]);
    }

    /**
     * Indicate that the product is popular (high stock).
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'stok' => $this->faker->numberBetween(50, 100),
            'harga' => $this->faker->numberBetween(20000, 60000),
        ]);
    }

    /**
     * Indicate that the product is premium (high price).
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'harga' => $this->faker->numberBetween(50000, 100000),
            'kategori' => 'premium',
            'nama' => $this->faker->randomElement([
                'Wagyu Steak',
                'Lobster Thermidor', 
                'Truffle Pasta',
                'Caviar Bowl',
                'Premium Sushi Set',
                'Aged Wine Selection',
                'Artisan Coffee',
                'Molecular Gastronomy'
            ]),
        ]);
    }

    /**
     * Create products for a specific merchant.
     */
    public function forMerchant(string $merchantId): static
    {
        return $this->state(fn (array $attributes) => [
            'merchant_id' => $merchantId,
        ]);
    }

    /**
     * Create products with specific category distribution.
     */
    public function withCategoryDistribution(): static
    {
        $rand = $this->faker->numberBetween(1, 100);
        
        if ($rand <= 40) {
            return $this->food();
        } elseif ($rand <= 70) {
            return $this->beverage();
        } elseif ($rand <= 90) {
            return $this->snack();
        } else {
            return $this->dessert();
        }
    }
}