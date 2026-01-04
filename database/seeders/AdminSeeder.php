<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate additional random merchants using factory (for testing purposes)
        if (app()->environment(['local', 'testing'])) {
            $user = User::create([
                'name' => 'Admin Ewallet',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]);
            
            $user->assignRole('admin');
            // $this->command->info('🏭 Additional test merchants created using factory');
        }

        $this->command->info('✅ Admins seeder completed successfully!');
        $this->command->info('📊 Total admins created: ' . User::count());
    }
}
