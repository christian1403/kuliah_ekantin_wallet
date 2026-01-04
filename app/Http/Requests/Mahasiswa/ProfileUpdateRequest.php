<?php

namespace App\Http\Requests\Mahasiswa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('mahasiswa');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $mahasiswa = $this->user()->mahasiswa;
        $userId = $this->user()->id;

        return [
            // User fields
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],

            // Mahasiswa fields
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jurusan' => ['required', 'string', 'max:100'],
            'angkatan' => [
                'required',
                'integer',
                'min:2000',
                'max:' . (date('Y') + 10) // Allow up to 10 years in the future
            ],

            // Optional fields
            'no_telepon' => ['nullable', 'string', 'max:20', 'regex:/^[\d\-\+\(\)\s]+$/'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional validation for angkatan vs current year
            if ($this->has('angkatan')) {
                $currentYear = (int) date('Y');
                $angkatan = (int) $this->angkatan;
                
                // Warn if angkatan is too far in the future
                if ($angkatan > $currentYear + 1) {
                    $validator->errors()->add(
                        'angkatan',
                        'Class year seems too far in the future.'
                    );
                }

                // Warn if angkatan is too old
                if ($angkatan < $currentYear - 10) {
                    $validator->errors()->add(
                        'angkatan',
                        'Class year seems too old. Please verify the year.'
                    );
                }
            }

            // Validate birth date is reasonable for a student
            if ($this->has('tanggal_lahir') && $this->tanggal_lahir) {
                $birthDate = \Carbon\Carbon::parse($this->tanggal_lahir);
                $age = $birthDate->age;
                
                if ($age < 16) {
                    $validator->errors()->add(
                        'tanggal_lahir',
                        'Age must be at least 16 years old.'
                    );
                }
                
                if ($age > 60) {
                    $validator->errors()->add(
                        'tanggal_lahir',
                        'Age cannot exceed 60 years old.'
                    );
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean phone number
        if ($this->has('no_telepon') && $this->no_telepon) {
            $this->merge([
                'no_telepon' => preg_replace('/[^\d\-\+\(\)\s]/', '', $this->no_telepon),
            ]);
        }

        // Normalize text fields
        if ($this->has('nama_lengkap')) {
            $this->merge([
                'nama_lengkap' => trim($this->nama_lengkap)
            ]);
        }

        if ($this->has('jurusan')) {
            $this->merge([
                'jurusan' => trim($this->jurusan)
            ]);
        }

        // Normalize gender field
        if ($this->has('jenis_kelamin')) {
            $this->merge([
                'jenis_kelamin' => strtoupper(trim($this->jenis_kelamin))
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
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name may not be greater than 255 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.',
            'nama_lengkap.required' => 'Full name is required.',
            'nama_lengkap.string' => 'Full name must be a string.',
            'nama_lengkap.max' => 'Full name may not be greater than 255 characters.',
            'jurusan.required' => 'Department/Major is required.',
            'jurusan.string' => 'Department/Major must be a string.',
            'jurusan.max' => 'Department/Major may not be greater than 100 characters.',
            'angkatan.required' => 'Class year is required.',
            'angkatan.integer' => 'Class year must be a number.',
            'angkatan.min' => 'Class year must be at least 2000.',
            'angkatan.max' => 'Class year is too far in the future.',
            'no_telepon.string' => 'Phone number must be a string.',
            'no_telepon.max' => 'Phone number may not be greater than 20 characters.',
            'no_telepon.regex' => 'Phone number format is invalid.',
            'alamat.string' => 'Address must be a string.',
            'alamat.max' => 'Address may not be greater than 500 characters.',
            'tanggal_lahir.date' => 'Birth date must be a valid date.',
            'tanggal_lahir.before' => 'Birth date must be before today.',
            'jenis_kelamin.in' => 'Gender must be either L (Male) or P (Female).',
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
            'name' => 'name',
            'email' => 'email address',
            'nama_lengkap' => 'full name',
            'jurusan' => 'department/major',
            'angkatan' => 'class year',
            'no_telepon' => 'phone number',
            'alamat' => 'address',
            'tanggal_lahir' => 'birth date',
            'jenis_kelamin' => 'gender',
        ];
    }
}
