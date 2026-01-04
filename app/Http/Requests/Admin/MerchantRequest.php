<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class MerchantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'nama_merchant' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:500'],
            'no_telepon' => ['required', 'string', 'max:20', 'regex:/^[\d\-\+\(\)\s]+$/'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean phone number
        if ($this->has('no_telepon')) {
            $this->merge([
                'no_telepon' => preg_replace('/[^\d\-\+\(\)\s]/', '', $this->no_telepon),
            ]);
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The merchant owner name is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.',
            'password.confirmed' => 'The password confirmation does not match.',
            'nama_merchant.required' => 'The merchant name is required.',
            'nama_merchant.string' => 'The merchant name must be a string.',
            'nama_merchant.max' => 'The merchant name may not be greater than 255 characters.',
            'alamat.required' => 'The address is required.',
            'alamat.string' => 'The address must be a string.',
            'alamat.max' => 'The address may not be greater than 500 characters.',
            'no_telepon.required' => 'The phone number is required.',
            'no_telepon.string' => 'The phone number must be a string.',
            'no_telepon.max' => 'The phone number may not be greater than 20 characters.',
            'no_telepon.regex' => 'The phone number format is invalid.',
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
            'name' => 'owner name',
            'email' => 'email address',
            'password' => 'password',
            'nama_merchant' => 'merchant name',
            'alamat' => 'address',
            'no_telepon' => 'phone number',
        ];
    }
}
