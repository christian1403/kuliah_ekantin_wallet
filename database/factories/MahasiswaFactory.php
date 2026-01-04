<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mahasiswa>
 */
class MahasiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $fullName = $firstName . ' ' . $lastName;
        
        // Generate NPM (Nomor Pokok Mahasiswa) - Indonesian student ID format
        $year = fake()->numberBetween(2020, 2024);
        $faculty = fake()->randomElement(['01', '02', '03', '04', '05', '06', '07', '08']);
        $sequence = fake()->unique()->numberBetween(1000, 9999);
        $npm = $year . $faculty . $sequence;
        
        return [
            'npm' => $npm,
            'email' => strtolower($firstName . '.' . $lastName . $npm) . '@student.ui.ac.id',
            'nama' => $fullName,
            'alamat' => fake()->address() . ', ' . fake()->city() . ', ' . fake()->state(),
            'tanggal_lahir' => fake()->dateTimeBetween('-25 years', '-17 years')->format('Y-m-d'),
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'no_hp' => '08' . fake()->numerify('##########'),
            'user_id' => null, // Default to no user account
        ];
    }

    /**
     * Create a mahasiswa with user account.
     */
    public function withUser(): static
    {
        return $this->afterCreating(function ($mahasiswa) {
            $user = User::create([
                'name' => $mahasiswa->nama,
                'email' => $mahasiswa->email,
                'password' => Hash::make('mahasiswa123'),
                'email_verified_at' => now(),
            ]);
            
            $user->assignRole('mahasiswa');
            $mahasiswa->update(['user_id' => $user->id]);
        });
    }

    /**
     * Create a mahasiswa without user account.
     */
    public function withoutUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    /**
     * Create a male mahasiswa.
     */
    public function male(): static
    {
        return $this->state(function (array $attributes) {
            $maleNames = [
                'Ahmad Rizki', 'Budi Santoso', 'Dedi Kurniawan', 'Eko Prasetyo',
                'Fajar Nugroho', 'Galih Pratama', 'Hendra Wijaya', 'Indra Gunawan',
                'Joko Susilo', 'Krisna Mahendra'
            ];
            
            return [
                'nama' => fake()->randomElement($maleNames) . ' ' . fake()->lastName(),
                'jenis_kelamin' => 'L',
            ];
        });
    }

    /**
     * Create a female mahasiswa.
     */
    public function female(): static
    {
        return $this->state(function (array $attributes) {
            $femaleNames = [
                'Aisyah Putri', 'Bella Safitri', 'Citra Dewi', 'Diana Sari',
                'Fitri Handayani', 'Gina Permata', 'Hana Melati', 'Indira Kartika',
                'Jihan Amelia', 'Kirana Wulandari'
            ];
            
            return [
                'nama' => fake()->randomElement($femaleNames) . ' ' . fake()->lastName(),
                'jenis_kelamin' => 'P',
            ];
        });
    }

    /**
     * Create a senior student (2020-2021).
     */
    public function senior(): static
    {
        return $this->state(function (array $attributes) {
            $year = fake()->numberBetween(2020, 2021);
            $faculty = fake()->randomElement(['01', '02', '03', '04', '05', '06', '07', '08']);
            $sequence = fake()->unique()->numberBetween(1000, 9999);
            
            return [
                'npm' => $year . $faculty . $sequence,
                'tanggal_lahir' => fake()->dateTimeBetween('-25 years', '-22 years')->format('Y-m-d'),
            ];
        });
    }

    /**
     * Create a junior student (2022-2024).
     */
    public function junior(): static
    {
        return $this->state(function (array $attributes) {
            $year = fake()->numberBetween(2022, 2024);
            $faculty = fake()->randomElement(['01', '02', '03', '04', '05', '06', '07', '08']);
            $sequence = fake()->unique()->numberBetween(1000, 9999);
            
            return [
                'npm' => $year . $faculty . $sequence,
                'tanggal_lahir' => fake()->dateTimeBetween('-20 years', '-17 years')->format('Y-m-d'),
            ];
        });
    }

    /**
     * Create a specific faculty mahasiswa.
     */
    public function faculty(string $facultyCode): static
    {
        return $this->state(function (array $attributes) use ($facultyCode) {
            $year = fake()->numberBetween(2020, 2024);
            $sequence = fake()->unique()->numberBetween(1000, 9999);
            
            return [
                'npm' => $year . $facultyCode . $sequence,
            ];
        });
    }

    /**
     * Create mahasiswa with specific email domain.
     */
    public function withEmailDomain(string $domain): static
    {
        return $this->state(function (array $attributes) use ($domain) {
            $username = strtolower(str_replace(' ', '.', $attributes['nama']));
            return [
                'email' => $username . '@' . $domain,
            ];
        });
    }
}
