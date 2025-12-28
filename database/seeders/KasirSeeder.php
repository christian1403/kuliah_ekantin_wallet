<?php

namespace Database\Seeders;

use App\Models\Kasir;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KasirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, ensure we have merchants to assign kasir to
        $merchants = Merchant::all();
        
        if ($merchants->isEmpty()) {
            $this->command->error('❌ No merchants found! Please run MerchantSeeder first.');
            return;
        }

        $this->command->info('🏪 Found ' . $merchants->count() . ' merchants for kasir assignment');

        // Predefined kasir data with realistic information
        $kasirData = [
            [
                'nama' => 'Ahmad Rizki Pratama',
                'email' => 'ahmad.kasir@warungbarokah.com',
                'no_hp' => '081234560001',
                'create_user' => true,
            ],
            [
                'nama' => 'Siti Nurhaliza',
                'email' => 'siti.kasir@kantinft.ac.id',
                'no_hp' => '081234560002',
                'create_user' => true,
            ],
            [
                'nama' => 'Budi Santoso',
                'email' => 'budi.kasir@studentcorner.com',
                'no_hp' => '081234560003',
                'create_user' => true,
            ],
            [
                'nama' => 'Dewi Lestari',
                'email' => 'dewi.kasir@alattulis.com',
                'no_hp' => '081234560004',
                'create_user' => true,
            ],
            [
                'nama' => 'Eko Prasetyo',
                'email' => 'eko.kasir@laundry24.com',
                'no_hp' => '081234560005',
                'create_user' => true,
            ],
            [
                'nama' => 'Fitri Handayani',
                'email' => 'fitri.kasir@printcenter.com',
                'no_hp' => '081234560006',
                'create_user' => true,
            ],
            [
                'nama' => 'Galih Praditya',
                'email' => 'galih.kasir@snackattack.com',
                'no_hp' => '081234560007',
                'create_user' => true,
            ],
            [
                'nama' => 'Hana Permata',
                'email' => 'hana.kasir@freshjuice.com',
                'no_hp' => '081234560008',
                'create_user' => true,
            ],
            [
                'nama' => 'Indra Kusuma',
                'email' => 'indra.kasir@bukuakademika.com',
                'no_hp' => '081234560009',
                'create_user' => true,
            ],
            [
                'nama' => 'Joko Widodo',
                'email' => 'joko.kasir@minimarket.edu',
                'no_hp' => '081234560010',
                'create_user' => true,
            ],
        ];

        // Additional kasir data for merchants without specific assignments
        $additionalKasirNames = [
            'Maya Sari', 'Novi Rahayu', 'Oki Setiawan', 'Putra Mahendra',
            'Qori Amelia', 'Rizal Fauzi', 'Siska Wulandari', 'Toni Kurniawan',
            'Umi Kalsum', 'Vina Melati', 'Wahyu Hidayat', 'Yesi Kartika'
        ];

        $createdKasir = 0;
        $createdUsers = 0;

        // Create kasir for merchants with predefined data
        foreach ($merchants->take(count($kasirData)) as $index => $merchant) {
            $kasir = $kasirData[$index];
            $userId = null;

            // Create user account if specified
            if ($kasir['create_user']) {
                $user = User::firstOrCreate(
                    ['email' => $kasir['email']],
                    [
                        'name' => $kasir['nama'],
                        'email' => $kasir['email'],
                        'password' => Hash::make('password123'),
                        'email_verified_at' => now(),
                    ]
                );

                $user->assignRole('kasir');
                $userId = $user->id;
                $createdUsers++;
            }

            // Create kasir
            Kasir::firstOrCreate(
                ['email' => $kasir['email']],
                [
                    'merchant_id' => $merchant->merchant_id,
                    'nama' => $kasir['nama'],
                    'email' => $kasir['email'],
                    'no_hp' => $kasir['no_hp'],
                    'user_id' => $userId,
                ]
            );
            $createdKasir++;
        }

        // Create additional kasir for remaining merchants (without user accounts initially)
        $remainingMerchants = $merchants->skip(count($kasirData));
        
        foreach ($remainingMerchants as $index => $merchant) {
            if ($index >= count($additionalKasirNames)) break;

            $nama = $additionalKasirNames[$index];
            $email = strtolower(str_replace(' ', '.', $nama)) . '@' . 'kasir.merchant.com';
            $noHp = '081234' . str_pad(560011 + $index, 6, '0', STR_PAD_LEFT);

            Kasir::firstOrCreate(
                ['email' => $email],
                [
                    'merchant_id' => $merchant->merchant_id,
                    'nama' => $nama,
                    'email' => $email,
                    'no_hp' => $noHp,
                    'user_id' => null, // No user account initially
                ]
            );
            $createdKasir++;
        }

        // Create some kasir with multiple employees per merchant (for larger merchants)
        $largeMerchants = $merchants->take(3); // First 3 merchants get additional kasir
        
        foreach ($largeMerchants as $index => $merchant) {
            $secondKasirNames = ['Assistant Manager', 'Shift Leader', 'Senior Cashier'];
            $nama = $merchant->nama . ' - ' . $secondKasirNames[$index];
            $email = 'manager' . ($index + 1) . '@' . strtolower(str_replace(' ', '', $merchant->nama)) . '.com';
            $noHp = '081234' . str_pad(570001 + $index, 6, '0', STR_PAD_LEFT);

            // Create user for managers
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $nama,
                    'email' => $email,
                    'password' => Hash::make('manager123'),
                    'email_verified_at' => now(),
                ]
            );
            $user->assignRole('kasir');
            $createdUsers++;

            Kasir::firstOrCreate(
                ['email' => $email],
                [
                    'merchant_id' => $merchant->merchant_id,
                    'nama' => $nama,
                    'email' => $email,
                    'no_hp' => $noHp,
                    'user_id' => $user->id,
                ]
            );
            $createdKasir++;
        }

        $this->command->info('✅ Kasir seeder completed successfully!');
        $this->command->info('👥 Total kasir created: ' . $createdKasir);
        $this->command->info('🔐 Total users created for kasir: ' . $createdUsers);
        $this->command->info('📊 Kasir with user accounts: ' . Kasir::whereNotNull('user_id')->count());
        $this->command->info('📊 Kasir without user accounts: ' . Kasir::whereNull('user_id')->count());
        
        // Show distribution by merchant
        $this->command->info('🏪 Kasir distribution by merchant:');
        $merchantKasirCount = Kasir::selectRaw('merchant_id, count(*) as kasir_count')
            ->groupBy('merchant_id')
            ->with('merchant:merchant_id,nama')
            ->get();
            
        foreach ($merchantKasirCount->take(5) as $item) {
            $merchantName = $item->merchant ? substr($item->merchant->nama, 0, 30) : 'Unknown';
            $this->command->info("   - {$merchantName}: {$item->kasir_count} kasir");
        }
    }
}
