<?php

namespace App\Http\Requests\Kasir;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'harga' => ['required', 'numeric', 'min:100', 'max:10000000'],
            'kategori' => ['required', 'string', 'max:100'],
            'stok' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'gambar' => ['nullable', 'string', 'max:255'],
            // 'status' => ['nullable', Rule::in(['active', 'inactive'])],
            // 'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert price to integer (remove formatting)
        if ($this->has('harga')) {
            $harga = preg_replace('/[^\d]/', '', $this->harga);
            $this->merge(['harga' => (int) $harga]);
        }

        // Set default values
        $this->merge([
            'stok' => $this->stok ?? 0,
            'status' => $this->status ?? 'active',
            'track_stock' => $this->track_stock ?? false,
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_produk.required' => 'Product name is required.',
            'nama_produk.string' => 'Product name must be a string.',
            'nama_produk.max' => 'Product name may not be greater than 255 characters.',
            'deskripsi.string' => 'Description must be a string.',
            'deskripsi.max' => 'Description may not be greater than 1000 characters.',
            'harga.required' => 'Price is required.',
            'harga.numeric' => 'Price must be a number.',
            'harga.min' => 'Price must be at least Rp 100.',
            'harga.max' => 'Price cannot exceed Rp 10,000,000.',
            'kategori.required' => 'Category is required.',
            'kategori.string' => 'Category must be a string.',
            'kategori.max' => 'Category may not be greater than 100 characters.',
            'stok.integer' => 'Stock must be a number.',
            'stok.min' => 'Stock cannot be negative.',
            'stok.max' => 'Stock cannot exceed 10,000 units.',
            'status.in' => 'Status must be either active or inactive.',
            'image.image' => 'File must be an image.',
            'image.mimes' => 'Image must be a file of type: jpeg, png, jpg, gif.',
            'image.max' => 'Image size cannot exceed 2MB.',
            'track_stock.boolean' => 'Stock tracking must be true or false.',
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
            'nama_produk' => 'product name',
            'deskripsi' => 'description',
            'harga' => 'price',
            'kategori' => 'category',
            'stok' => 'stock quantity',
            'status' => 'product status',
            'image' => 'product image',
            'track_stock' => 'stock tracking',
        ];
    }
}
