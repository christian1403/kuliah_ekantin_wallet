<?php

namespace App\Http\Requests\Mahasiswa;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
        return [
            'recipient_nim' => [
                'required',
                'string',
                'exists:mahasiswa,nim',
                'different:current_nim' // Will be set in prepareForValidation
            ],
            'amount' => [
                'required',
                'numeric',
                'min:1000',         // Minimum Rp 1,000
                'max:1000000',      // Maximum Rp 1,000,000 per transaction
                'regex:/^[0-9]+$/'  // Only integers allowed
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'pin' => ['nullable', 'string', 'min:4', 'max:6'], // For future PIN validation
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $mahasiswa = $this->user()->mahasiswa;
            
            if ($mahasiswa) {
                // Check sufficient balance
                $currentBalance = $mahasiswa->wallet->saldo ?? 0;
                if ($this->amount > $currentBalance) {
                    $validator->errors()->add(
                        'amount',
                        'Insufficient balance. Available: Rp ' . number_format($currentBalance, 0, ',', '.')
                    );
                }

                // Check daily transfer limit
                $todayTransfers = \App\Models\WalletTransaction::where('mahasiswa_id', $mahasiswa->id)
                    ->where('type', 'debit')
                    ->whereHas('detailPemasukan') // Only count transfers, not purchases
                    ->whereDate('created_at', today())
                    ->sum('amount');

                $dailyTransferLimit = 5000000; // Rp 5,000,000 daily transfer limit
                $remainingLimit = $dailyTransferLimit - $todayTransfers;

                if ($this->amount > $remainingLimit) {
                    $validator->errors()->add(
                        'amount',
                        'Transfer amount exceeds daily limit. Remaining limit: Rp ' . number_format($remainingLimit, 0, ',', '.')
                    );
                }

                // Validate recipient exists and is not the same person
                if ($this->recipient_nim) {
                    $recipient = \App\Models\Mahasiswa::where('nim', $this->recipient_nim)->first();
                    
                    if (!$recipient) {
                        $validator->errors()->add(
                            'recipient_nim',
                            'Recipient student not found.'
                        );
                    } elseif ($recipient->id === $mahasiswa->id) {
                        $validator->errors()->add(
                            'recipient_nim',
                            'Cannot transfer to yourself.'
                        );
                    } elseif (!$recipient->wallet) {
                        $validator->errors()->add(
                            'recipient_nim',
                            'Recipient wallet not found.'
                        );
                    }
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean amount (remove formatting)
        if ($this->has('amount')) {
            $amount = preg_replace('/[^\d]/', '', $this->amount);
            $this->merge(['amount' => (int) $amount]);
        }

        // Set current user's NIM for validation
        $mahasiswa = $this->user()->mahasiswa;
        if ($mahasiswa) {
            $this->merge(['current_nim' => $mahasiswa->nim]);
        }

        // Clean recipient NIM
        if ($this->has('recipient_nim')) {
            $this->merge([
                'recipient_nim' => trim(strtoupper($this->recipient_nim))
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
            'recipient_nim.required' => 'Recipient student ID (NIM) is required.',
            'recipient_nim.string' => 'Recipient NIM must be a string.',
            'recipient_nim.exists' => 'Recipient student not found.',
            'recipient_nim.different' => 'Cannot transfer to yourself.',
            'amount.required' => 'Transfer amount is required.',
            'amount.numeric' => 'Transfer amount must be a number.',
            'amount.min' => 'Minimum transfer amount is Rp 1,000.',
            'amount.max' => 'Maximum transfer amount is Rp 1,000,000.',
            'amount.regex' => 'Transfer amount must be a whole number.',
            'description.string' => 'Description must be a string.',
            'description.max' => 'Description may not be greater than 255 characters.',
            'pin.string' => 'PIN must be a string.',
            'pin.min' => 'PIN must be at least 4 digits.',
            'pin.max' => 'PIN may not be greater than 6 digits.',
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
            'recipient_nim' => 'recipient NIM',
            'amount' => 'transfer amount',
            'description' => 'transfer description',
            'pin' => 'security PIN',
        ];
    }
}
