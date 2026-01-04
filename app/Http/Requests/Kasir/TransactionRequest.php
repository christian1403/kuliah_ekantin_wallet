<?php

namespace App\Http\Requests\Kasir;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Produk;

class TransactionRequest extends FormRequest
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
            'nim' => ['required', 'string', 'exists:mahasiswa,nim'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.produk_id' => ['required', 'string', 'exists:produk,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:100'],
            'metode_bayar_id' => ['required', 'string', 'exists:metode_bayar,id'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('items')) {
                $kasir = $this->user()->kasir;
                
                foreach ($this->items as $index => $item) {
                    // Check if product belongs to the kasir's merchant
                    $produk = Produk::where('id', $item['produk_id'] ?? '')
                        ->where('merchant_id', $kasir->merchant_id ?? '')
                        ->where('status', 'active')
                        ->first();
                    
                    if (!$produk) {
                        $validator->errors()->add(
                            "items.{$index}.produk_id",
                            'Product not available or does not belong to your merchant.'
                        );
                        continue;
                    }
                    
                    // Check stock availability if stock tracking is enabled
                    if ($produk->track_stock && $produk->stok < ($item['qty'] ?? 0)) {
                        $validator->errors()->add(
                            "items.{$index}.qty",
                            "Insufficient stock for {$produk->nama_produk}. Available: {$produk->stok}"
                        );
                    }
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
            'nim.required' => 'Student ID (NIM) is required.',
            'nim.exists' => 'Student not found. Please check the NIM.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.produk_id.required' => 'Product is required for each item.',
            'items.*.produk_id.exists' => 'Selected product is invalid.',
            'items.*.qty.required' => 'Quantity is required for each item.',
            'items.*.qty.integer' => 'Quantity must be a number.',
            'items.*.qty.min' => 'Quantity must be at least 1.',
            'items.*.qty.max' => 'Quantity cannot exceed 100.',
            'metode_bayar_id.required' => 'Payment method is required.',
            'metode_bayar_id.exists' => 'Selected payment method is invalid.',
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
            'nim' => 'student ID',
            'items' => 'transaction items',
            'items.*.produk_id' => 'product',
            'items.*.qty' => 'quantity',
            'metode_bayar_id' => 'payment method',
            'catatan' => 'notes',
        ];
    }
}
