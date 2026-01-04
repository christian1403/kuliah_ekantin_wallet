<?php

namespace Database\Seeders;

use App\Models\MetodeBayar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MetodeBayarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏦 Creating payment methods...');

        // Generate additional payment methods using factory (for testing purposes)
        if (app()->environment(['local', 'testing'])) {
            // Create additional bank transfer methods
            // MetodeBayar::factory(2)->bankTransfer()->create();
            
            // Create additional e-wallet methods
            // MetodeBayar::factory(2)->eWallet()->create();
            
            // Create credit card methods
            // MetodeBayar::factory(2)->creditCard()->create();
            
            // Create virtual account methods
            // MetodeBayar::factory(2)->virtualAccount()->create();

            MetodeBayar::factory(1)->cash()->create();
            
            $this->command->info('🔧 Additional test payment methods created using factory');
        }

        $this->command->info('✅ MetodeBayar seeder completed successfully!');
        $this->command->info('📊 Total payment methods created: ' . MetodeBayar::count());
    }
}