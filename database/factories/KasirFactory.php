<?php

namespace Database\Factories;

use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kasir>
 */
class KasirFactory extends Factory
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
        
        return [
            'merchant_id' => Merchant::factory(),
            'nama' => $fullName,
            'email' => strtolower($firstName . '.' . $lastName) . '@kasir.com',
            'no_hp' => '08' . fake()->numerify('##########'),
            'user_id' => null, // Default to no user account
        ];
    }

    /**
     * Create a kasir with existing merchant.
     */
    public function forMerchant(string $merchantId): static
    {
        return $this->state(fn (array $attributes) => [
            'merchant_id' => $merchantId,
        ]);
    }

    /**
     * Create a kasir with user account.
     */
    public function withUser(): static
    {
        return $this->afterCreating(function ($kasir) {
            $user = User::create([
                'name' => $kasir->nama,
                'email' => $kasir->email,
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);
            
            $user->assignRole('kasir');
            $kasir->update(['user_id' => $user->id]);
        });
    }

    /**
     * Create a kasir without user account.
     */
    public function withoutUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    /**
     * Create a manager-level kasir.
     */
    public function manager(): static
    {
        return $this->state(function (array $attributes) {
            $positions = ['Manager', 'Assistant Manager', 'Shift Supervisor', 'Senior Cashier'];
            $position = fake()->randomElement($positions);
            
            return [
                'nama' => $attributes['nama'] . ' - ' . $position,
                'email' => 'manager.' . fake()->unique()->userName() . '@kasir.com',
            ];
        })->withUser();
    }

    /**
     * Create a regular staff kasir.
     */
    public function staff(): static
    {
        return $this->state(function (array $attributes) {
            $positions = ['Cashier', 'Staff', 'Assistant'];
            $position = fake()->randomElement($positions);
            
            return [
                'nama' => $attributes['nama'] . ' - ' . $position,
                'email' => 'staff.' . fake()->unique()->userName() . '@kasir.com',
            ];
        });
    }

    /**
     * Create kasir with specific merchant type email domain.
     */
    public function withMerchantEmail(): static
    {
        return $this->afterMaking(function ($kasir) {
            if ($kasir->merchant) {
                $domain = strtolower(str_replace([' ', '-', '_'], '', $kasir->merchant->nama)) . '.com';
                $username = strtolower(str_replace(' ', '.', $kasir->nama));
                $kasir->email = $username . '@' . $domain;
            }
        });
    }
}
