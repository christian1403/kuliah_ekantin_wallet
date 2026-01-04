<?php

namespace Database\Seeders;

use App\Models\Merchant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate additional random merchants using factory (for testing purposes)
        if (app()->environment(['local', 'testing'])) {
            // Create 5 random food merchants
            Merchant::factory(5)->foodMerchant()->create();
            
            // Create 3 random service merchants
            Merchant::factory(3)->serviceMerchant()->create();
            
            // Create 2 random retail merchants
            Merchant::factory(2)->retailMerchant()->create();
            
            $this->command->info('🏭 Additional test merchants created using factory');
        }

        $this->command->info('✅ Merchant seeder completed successfully!');
        $this->command->info('📊 Total merchants created: ' . Merchant::count());
    }
}
