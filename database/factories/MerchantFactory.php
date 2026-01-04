<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Merchant>
 */
class MerchantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $businessTypes = [
            'Warung', 'Kantin', 'Cafe', 'Toko', 'Restoran', 'Kedai',
            'Laundry', 'Fotocopy', 'Minimarket', 'Bakery', 'Juice Bar'
        ];
        
        $businessNames = [
            'Barokah', 'Sederhana', 'Jaya', 'Makmur', 'Berkah', 'Sukses',
            'Express', 'Premium', 'Corner', 'Center', 'Paradise', 'Fresh'
        ];
        
        $businessType = fake()->randomElement($businessTypes);
        $businessName = fake()->randomElement($businessNames);
        
        return [
            'kode_merchant' => strtoupper($businessType) . fake()->unique()->numberBetween(100, 999),
            'nama' => $businessType . ' ' . $businessName . ' ' . fake()->city(),
            'email' => strtolower($businessType . $businessName) . '@' . fake()->safeEmailDomain(),
            'alamat' => fake()->streetAddress() . ', ' . fake()->city() . ', ' . fake()->state(),
            'no_hp' => '08' . fake()->numerify('##########'),
        ];
    }

    /**
     * Create a merchant without kode_merchant.
     */
    public function withoutCode(): static
    {
        return $this->state(fn (array $attributes) => [
            'kode_merchant' => null,
        ]);
    }

    /**
     * Create a food-related merchant.
     */
    public function foodMerchant(): static
    {
        $foodTypes = ['Warung', 'Kantin', 'Restoran', 'Kedai', 'Cafe'];
        $foodNames = ['Nasi Padang', 'Bakso', 'Soto', 'Ayam Geprek', 'Mie Ayam'];
        
        return $this->state(fn (array $attributes) => [
            'nama' => fake()->randomElement($foodTypes) . ' ' . fake()->randomElement($foodNames) . ' ' . fake()->firstName(),
            'email' => 'food' . fake()->unique()->numberBetween(1, 1000) . '@merchant.com',
        ]);
    }

    /**
     * Create a service-related merchant.
     */
    public function serviceMerchant(): static
    {
        $serviceTypes = ['Laundry', 'Fotocopy', 'Print', 'Salon', 'Bengkel'];
        
        return $this->state(fn (array $attributes) => [
            'nama' => fake()->randomElement($serviceTypes) . ' ' . fake()->company(),
            'email' => 'service' . fake()->unique()->numberBetween(1, 1000) . '@merchant.com',
        ]);
    }

    /**
     * Create a retail merchant.
     */
    public function retailMerchant(): static
    {
        $retailTypes = ['Toko', 'Minimarket', 'Supermarket', 'Grosir'];
        $retailItems = ['Alat Tulis', 'Buku', 'Fashion', 'Elektronik', 'Sembako'];
        
        return $this->state(fn (array $attributes) => [
            'nama' => fake()->randomElement($retailTypes) . ' ' . fake()->randomElement($retailItems),
            'email' => 'retail' . fake()->unique()->numberBetween(1, 1000) . '@merchant.com',
        ]);
    }
}
