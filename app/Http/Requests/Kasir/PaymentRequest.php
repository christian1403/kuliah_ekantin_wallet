<?php

namespace App\Http\Requests\Kasir;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Mahasiswa;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('kasir');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mahasiswa_id' => ['required', 'string', 'exists:mahasiswa,id'],
            'total_amount' => ['required', 'numeric', 'min:100', 'max:10000000'],
            'metode_bayar_id' => ['required', 'string', 'exists:metode_bayar,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.produk_id' => ['required', 'string', 'exists:produk,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.harga_satuan' => ['required', 'numeric', 'min:100'],
            'items.*.subtotal' => ['required', 'numeric', 'min:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'tax_amount' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate mahasiswa has sufficient balance
            if ($this->has('mahasiswa_id') && $this->has('total_amount')) {
                $mahasiswa = Mahasiswa::find($this->mahasiswa_id);
                
                if ($mahasiswa && $mahasiswa->wallet) {
                    if ($mahasiswa->wallet->saldo < $this->total_amount) {
                        $validator->errors()->add(
                            'total_amount',
                            'Student has insufficient balance. Available: Rp ' . number_format($mahasiswa->wallet->saldo, 0, ',', '.')
                        );
                    }
                } else {
                    $validator->errors()->add(
                        'mahasiswa_id',
                        'Student wallet not found.'
                    );
                }
            }

            // Validate total calculation
            if ($this->has('items') && $this->has('total_amount')) {
                $calculatedTotal = 0;
                
                foreach ($this->items as $item) {
                    if (isset($item['qty']) && isset($item['harga_satuan'])) {
                        $expectedSubtotal = $item['qty'] * $item['harga_satuan'];
                        
                        if (isset($item['subtotal']) && abs($item['subtotal'] - $expectedSubtotal) > 1) {
                            $validator->errors()->add(
                                'items',
                                'Item subtotal calculation is incorrect.'
                            );
                        }
                        
                        $calculatedTotal += $expectedSubtotal;
                    }
                }

                // Add discount and tax
                $calculatedTotal -= $this->discount_amount ?? 0;
                $calculatedTotal += $this->tax_amount ?? 0;

                if (abs($calculatedTotal - $this->total_amount) > 1) {
                    $validator->errors()->add(
                        'total_amount',
                        'Total amount calculation is incorrect.'
                    );
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mahasiswa_id.required' => 'Student is required.',
            'mahasiswa_id.exists' => 'Selected student is invalid.',
            'total_amount.required' => 'Total amount is required.',
            'total_amount.numeric' => 'Total amount must be a number.',
            'total_amount.min' => 'Total amount must be at least Rp 100.',
            'total_amount.max' => 'Total amount cannot exceed Rp 10,000,000.',
            'metode_bayar_id.required' => 'Payment method is required.',
            'metode_bayar_id.exists' => 'Selected payment method is invalid.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.produk_id.required' => 'Product is required for each item.',
            'items.*.produk_id.exists' => 'Selected product is invalid.',
            'items.*.qty.required' => 'Quantity is required for each item.',
            'items.*.qty.integer' => 'Quantity must be a number.',
            'items.*.qty.min' => 'Quantity must be at least 1.',
            'items.*.qty.max' => 'Quantity cannot exceed 100.',
            'items.*.harga_satuan.required' => 'Unit price is required for each item.',
            'items.*.harga_satuan.numeric' => 'Unit price must be a number.',
            'items.*.harga_satuan.min' => 'Unit price must be at least Rp 100.',
            'items.*.subtotal.required' => 'Subtotal is required for each item.',
            'items.*.subtotal.numeric' => 'Subtotal must be a number.',
            'items.*.subtotal.min' => 'Subtotal must be at least Rp 100.',
            'discount_amount.numeric' => 'Discount amount must be a number.',
            'discount_amount.min' => 'Discount amount cannot be negative.',
            'discount_amount.max' => 'Discount amount cannot exceed Rp 1,000,000.',
            'tax_amount.numeric' => 'Tax amount must be a number.',
            'tax_amount.min' => 'Tax amount cannot be negative.',
            'tax_amount.max' => 'Tax amount cannot exceed Rp 1,000,000.',
            'catatan.max' => 'Notes may not be greater than 500 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'mahasiswa_id' => 'student',
            'total_amount' => 'total amount',
            'metode_bayar_id' => 'payment method',
            'items' => 'transaction items',
            'items.*.produk_id' => 'product',
            'items.*.qty' => 'quantity',
            'items.*.harga_satuan' => 'unit price',
            'items.*.subtotal' => 'subtotal',
            'discount_amount' => 'discount amount',
            'tax_amount' => 'tax amount',
            'catatan' => 'notes',
        ];
    }
}
