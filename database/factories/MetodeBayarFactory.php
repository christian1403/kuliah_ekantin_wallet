<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MetodeBayar>
 */
class MetodeBayarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $paymentMethods = [
            'Bank Transfer BCA',
            'Bank Transfer Mandiri', 
            'Bank Transfer BRI',
            'Bank Transfer BNI',
            'E-Wallet OVO',
            'E-Wallet GoPay',
            'E-Wallet Dana',
            'E-Wallet ShopeePay',
            'Credit Card Visa',
            'Credit Card Mastercard',
            'Virtual Account BCA',
            'Virtual Account Mandiri',
            'QRIS Payment',
            'Cash Payment',
            'Debit Card'
        ];
        
        return [
            'nama' => fake()->randomElement($paymentMethods),
        ];
    }

    /**
     * Configure factory for bank transfer payment methods.
     */
    public function bankTransfer(): static
    {
        return $this->state(function (array $attributes) {
            $banks = ['BCA', 'Mandiri', 'BRI', 'BNI', 'CIMB', 'Danamon'];
            $bank = fake()->randomElement($banks);
            
            return [
                'nama' => "Bank Transfer {$bank}",
            ];
        });
    }

    /**
     * Configure factory for e-wallet payment methods.
     */
    public function eWallet(): static
    {
        return $this->state(function (array $attributes) {
            $wallets = ['OVO', 'GoPay', 'Dana', 'ShopeePay', 'LinkAja'];
            $wallet = fake()->randomElement($wallets);
            
            return [
                'nama' => "E-Wallet {$wallet}",
            ];
        });
    }

    /**
     * Configure factory for credit card payment methods.
     */
    public function creditCard(): static
    {
        return $this->state(function (array $attributes) {
            $cards = ['Visa', 'Mastercard', 'JCB', 'American Express'];
            $card = fake()->randomElement($cards);
            
            return [
                'nama' => "Credit Card {$card}",
            ];
        });
    }

    /**
     * Configure factory for virtual account payment methods.
     */
    public function virtualAccount(): static
    {
        return $this->state(function (array $attributes) {
            $banks = ['BCA', 'Mandiri', 'BRI', 'BNI', 'Permata'];
            $bank = fake()->randomElement($banks);
            
            return [
                'nama' => "Virtual Account {$bank}",
            ];
        });
    }

    /**
     * Configure factory for QRIS payment method.
     */
    public function qris(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'nama' => 'QRIS Payment',
            ];
        });
    }

    /**
     * Configure factory for cash payment method.
     */
    public function cash(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'nama' => 'Cash Payment (Admin Confirmed)',
            ];
        });
    }
}