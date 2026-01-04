<?php

namespace Database\Seeders;

use App\Models\Produk;
use App\Models\Merchant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have merchants before creating products
        $merchantCount = Merchant::count();
        
        if ($merchantCount === 0) {
            $this->command->info('No merchants found. Creating merchants first...');
            Merchant::factory()->count(5)->create();
            $merchantCount = 5;
        }

        $this->command->info("Creating products for {$merchantCount} merchants...");

        // Clear existing products
        // DB::table('produks')->truncate();

        // Get all merchants
        $merchants = Merchant::all();

        foreach ($merchants as $merchant) {
            $this->command->info("Creating products for merchant: {$merchant->nama_merchant}");
            
            // Create a variety of products for each merchant
            // Each merchant gets 8-15 products with different categories
            $productCount = rand(8, 15);
            
            // Create food products (40% of total)
            $foodCount = ceil($productCount * 0.4);
            Produk::factory()
                ->count($foodCount)
                ->food()
                ->forMerchant($merchant->merchant_id)
                ->create();

            // Create beverage products (30% of total)
            $beverageCount = ceil($productCount * 0.3);
            Produk::factory()
                ->count($beverageCount)
                ->beverage()
                ->forMerchant($merchant->merchant_id)
                ->create();

            // Create snack products (20% of total)
            $snackCount = ceil($productCount * 0.2);
            Produk::factory()
                ->count($snackCount)
                ->snack()
                ->forMerchant($merchant->merchant_id)
                ->create();

            // Create dessert products (10% of total)
            $dessertCount = max(1, floor($productCount * 0.1));
            Produk::factory()
                ->count($dessertCount)
                ->dessert()
                ->forMerchant($merchant->merchant_id)
                ->create();
        }

        // Create some special products
        $this->createSpecialProducts();
        
        $totalProducts = Produk::count();
        $this->command->info("Created {$totalProducts} products successfully!");
        
        // Display summary
        $this->displaySummary();
    }

    /**
     * Create special products with specific characteristics.
     */
    private function createSpecialProducts(): void
    {
        $merchants = Merchant::take(3)->get();
        
        if ($merchants->count() < 3) {
            return;
        }

        // Create some premium products
        Produk::factory()
            ->count(3)
            ->premium()
            ->forMerchant($merchants->first()->merchant_id)
            ->create();

        // Create some popular products
        Produk::factory()
            ->count(5)
            ->popular()
            ->forMerchant($merchants->get(1)->merchant_id)
            ->create();

        // Create some out of stock products
        Produk::factory()
            ->count(2)
            ->outOfStock()
            ->forMerchant($merchants->last()->merchant_id)
            ->create();

        $this->command->info('Created special products: 3 premium, 5 popular, 2 out of stock');
    }

    /**
     * Display a summary of created products.
     */
    private function displaySummary(): void
    {
        $this->command->info('=== PRODUCT SEEDING SUMMARY ===');
        
        // Products by merchant
        $merchantSummary = DB::table('produks')
            ->join('merchants', 'produks.merchant_id', '=', 'merchants.merchant_id')
            ->select('merchants.nama', DB::raw('count(*) as product_count'))
            ->groupBy('merchants.nama', 'merchants.merchant_id')
            ->get();

        $this->command->info('Products per merchant:');
        foreach ($merchantSummary as $summary) {
            $this->command->info("  • {$summary->nama}: {$summary->product_count} products");
        }

        // Stock summary
        $stockSummary = [
            'in_stock' => Produk::where('stok', '>', 0)->count(),
            'out_of_stock' => Produk::where('stok', '=', 0)->count(),
            'low_stock' => Produk::whereBetween('stok', [1, 10])->count(),
        ];

        $this->command->info('Stock status:');
        $this->command->info("  • In Stock: {$stockSummary['in_stock']} products");
        $this->command->info("  • Out of Stock: {$stockSummary['out_of_stock']} products");
        $this->command->info("  • Low Stock (≤10): {$stockSummary['low_stock']} products");

        // Price ranges
        $priceRanges = [
            'budget' => Produk::where('harga', '<', 10000)->count(),
            'mid_range' => Produk::whereBetween('harga', [10000, 30000])->count(),
            'premium' => Produk::where('harga', '>', 30000)->count(),
        ];

        $this->command->info('Price ranges:');
        $this->command->info("  • Budget (<Rp10,000): {$priceRanges['budget']} products");
        $this->command->info("  • Mid-range (Rp10,000-30,000): {$priceRanges['mid_range']} products");
        $this->command->info("  • Premium (>Rp30,000): {$priceRanges['premium']} products");

        // Category breakdown
        $categoryBreakdown = [
            'food' => Produk::where('kategori', 'food')->count(),
            'beverage' => Produk::where('kategori', 'beverage')->count(),
            'snack' => Produk::where('kategori', 'snack')->count(),
            'dessert' => Produk::where('kategori', 'dessert')->count(),
            'premium' => Produk::where('kategori', 'premium')->count(),
        ];

        $this->command->info('Category breakdown:');
        $this->command->info("  • Food: {$categoryBreakdown['food']} products");
        $this->command->info("  • Beverage: {$categoryBreakdown['beverage']} products");
        $this->command->info("  • Snack: {$categoryBreakdown['snack']} products");
        $this->command->info("  • Dessert: {$categoryBreakdown['dessert']} products");
        $this->command->info("  • Premium: {$categoryBreakdown['premium']} products");

        // Average prices per merchant
        $avgPrices = DB::table('produks')
            ->join('merchants', 'produks.merchant_id', '=', 'merchants.merchant_id')
            ->select('merchants.nama', DB::raw('AVG(produks.harga) as avg_price'))
            ->groupBy('merchants.nama', 'merchants.merchant_id')
            ->get();

        $this->command->info('Average prices per merchant:');
        foreach ($avgPrices as $avg) {
            $formattedPrice = 'Rp' . number_format($avg->avg_price, 0, ',', '.');
            $this->command->info("  • {$avg->nama}: {$formattedPrice}");
        }

        $this->command->info('=== END SUMMARY ===');
    }
}