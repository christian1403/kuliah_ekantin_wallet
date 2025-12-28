<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👨‍🎓 Starting Mahasiswa seeder...');

        // Predefined mahasiswa data with realistic Indonesian student information
        $mahasiswaData = [
            [
                'npm' => '2021010001',
                'nama' => 'Ahmad Rizki Pratama',
                'email' => 'ahmad.rizki.2021010001@student.ui.ac.id',
                'alamat' => 'Jl. Margonda Raya No. 123, Depok, Jawa Barat',
                'tanggal_lahir' => '2002-03-15',
                'jenis_kelamin' => 'L',
                'no_hp' => '081234567801',
                'create_user' => true,
            ],
            [
                'npm' => '2021020002',
                'nama' => 'Siti Nurhaliza Putri',
                'email' => 'siti.nurhaliza.2021020002@student.ui.ac.id',
                'alamat' => 'Jl. Kukusan Raya No. 45, Beji, Depok',
                'tanggal_lahir' => '2003-07-22',
                'jenis_kelamin' => 'P',
                'no_hp' => '081234567802',
                'create_user' => true,
            ],
            [
                'npm' => '2022030003',
                'nama' => 'Budi Santoso Wijaya',
                'email' => 'budi.santoso.2022030003@student.ui.ac.id',
                'alamat' => 'Jl. Cinere Raya No. 78, Cinere, Depok',
                'tanggal_lahir' => '2003-11-08',
                'jenis_kelamin' => 'L',
                'no_hp' => '081234567803',
                'create_user' => true,
            ],
            [
                'npm' => '2022040004',
                'nama' => 'Dewi Lestari Handayani',
                'email' => 'dewi.lestari.2022040004@student.ui.ac.id',
                'alamat' => 'Jl. Raya Bogor KM 7, Cimanggis, Depok',
                'tanggal_lahir' => '2004-01-17',
                'jenis_kelamin' => 'P',
                'no_hp' => '081234567804',
                'create_user' => true,
            ],
            [
                'npm' => '2023050005',
                'nama' => 'Eko Prasetyo Nugroho',
                'email' => 'eko.prasetyo.2023050005@student.ui.ac.id',
                'alamat' => 'Jl. Sawangan Raya No. 56, Sawangan, Depok',
                'tanggal_lahir' => '2004-09-03',
                'jenis_kelamin' => 'L',
                'no_hp' => '081234567805',
                'create_user' => true,
            ],
            [
                'npm' => '2023060006',
                'nama' => 'Fitri Handayani Sari',
                'email' => 'fitri.handayani.2023060006@student.ui.ac.id',
                'alamat' => 'Jl. Raya Pancoran MAS No. 34, Pancoran MAS, Depok',
                'tanggal_lahir' => '2005-04-12',
                'jenis_kelamin' => 'P',
                'no_hp' => '081234567806',
                'create_user' => true,
            ],
            [
                'npm' => '2020070007',
                'nama' => 'Galih Praditya Mahendra',
                'email' => 'galih.praditya.2020070007@student.ui.ac.id',
                'alamat' => 'Jl. Lenteng Agung Raya No. 89, Lenteng Agung, Jakarta Selatan',
                'tanggal_lahir' => '2001-12-25',
                'jenis_kelamin' => 'L',
                'no_hp' => '081234567807',
                'create_user' => true,
            ],
            [
                'npm' => '2020080008',
                'nama' => 'Hana Permatasari Melati',
                'email' => 'hana.permata.2020080008@student.ui.ac.id',
                'alamat' => 'Jl. TB Simatupang KM 1, Kebagusan, Jakarta Selatan',
                'tanggal_lahir' => '2002-06-14',
                'jenis_kelamin' => 'P',
                'no_hp' => '081234567808',
                'create_user' => true,
            ],
            [
                'npm' => '2021010009',
                'nama' => 'Indra Kusuma Wardana',
                'email' => 'indra.kusuma.2021010009@student.ui.ac.id',
                'alamat' => 'Jl. Raya Parung No. 123, Parung, Bogor',
                'tanggal_lahir' => '2002-10-30',
                'jenis_kelamin' => 'L',
                'no_hp' => '081234567809',
                'create_user' => true,
            ],
            [
                'npm' => '2024020010',
                'nama' => 'Jihan Amelia Putri',
                'email' => 'jihan.amelia.2024020010@student.ui.ac.id',
                'alamat' => 'Jl. Raya Cibinong No. 67, Cibinong, Bogor',
                'tanggal_lahir' => '2005-08-19',
                'jenis_kelamin' => 'P',
                'no_hp' => '081234567810',
                'create_user' => true,
            ],
        ];

        $createdMahasiswa = 0;
        $createdUsers = 0;

        // Create predefined mahasiswa
        foreach ($mahasiswaData as $data) {
            $userId = null;

            // Create user account if specified
            if ($data['create_user']) {
                $user = User::firstOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['nama'],
                        'email' => $data['email'],
                        'password' => Hash::make('mahasiswa123'),
                        'email_verified_at' => now(),
                    ]
                );
                $userId = $user->id;
                $createdUsers++;
            }

            // Create mahasiswa
            Mahasiswa::firstOrCreate(
                ['npm' => $data['npm']],
                [
                    'npm' => $data['npm'],
                    'nama' => $data['nama'],
                    'email' => $data['email'],
                    'alamat' => $data['alamat'],
                    'tanggal_lahir' => $data['tanggal_lahir'],
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'no_hp' => $data['no_hp'],
                    'user_id' => $userId,
                ]
            );
            $createdMahasiswa++;
        }

        // Generate additional mahasiswa using factory (for testing purposes)
        if (app()->environment(['local', 'testing'])) {
            // Create senior students (2020-2021) - some with user accounts
            Mahasiswa::factory(5)->senior()->male()->withUser()->create();
            Mahasiswa::factory(5)->senior()->female()->withUser()->create();
            // Mahasiswa::factory(3)->senior()->male()->withoutUser()->create();
            // Mahasiswa::factory(2)->senior()->female()->withoutUser()->create();

            // Create junior students (2022-2024) - mixed user accounts
            Mahasiswa::factory(8)->junior()->withUser()->create();
            // Mahasiswa::factory(5)->junior()->withoutUser()->create();

            // Create students from different faculties
            $faculties = ['01', '02', '03', '04', '05'];
            foreach ($faculties as $facultyCode) {
                Mahasiswa::factory(2)->faculty($facultyCode)->withUser()->create();
            }

            $factoryCreated = 15 + 13 + 15; // senior + junior + faculty students
            $createdMahasiswa += $factoryCreated;
            
            $factoryUsers = 15 + 8 + 10; // Users created by factory
            $createdUsers += $factoryUsers;

            $this->command->info('🏫 Additional test mahasiswa created using factory');
        }

        $this->command->info('✅ Mahasiswa seeder completed successfully!');
        $this->command->info('👨‍🎓 Total mahasiswa created: ' . $createdMahasiswa);
        $this->command->info('🔐 Total users created for mahasiswa: ' . $createdUsers);
        $this->command->info('📊 Mahasiswa with user accounts: ' . Mahasiswa::whereNotNull('user_id')->count());
        $this->command->info('📊 Mahasiswa without user accounts: ' . Mahasiswa::whereNull('user_id')->count());

        // Show statistics by gender and year
        $this->command->info('📊 Statistics:');
        $maleCount = Mahasiswa::where('jenis_kelamin', 'L')->count();
        $femaleCount = Mahasiswa::where('jenis_kelamin', 'P')->count();
        $this->command->info("   - Male students: {$maleCount}");
        $this->command->info("   - Female students: {$femaleCount}");

        // Show distribution by year (based on NPM)
        $yearStats = Mahasiswa::selectRaw('LEFT(npm, 4) as year, COUNT(*) as count')
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        $this->command->info('📅 Distribution by year:');
        foreach ($yearStats as $stat) {
            $this->command->info("   - {$stat->year}: {$stat->count} students");
        }
    }
}
