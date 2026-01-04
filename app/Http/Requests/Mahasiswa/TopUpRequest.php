<?php

namespace App\Http\Requests\Mahasiswa;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\MetodeBayar;

class TopUpRequest extends FormRequest
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
            'amount' => [
                'required',
                'numeric',
                'min:10000',        // Minimum Rp 10,000
                'max:5000000',      // Maximum Rp 5,000,000 per top-up
                'regex:/^[0-9]+$/'  // Only integers allowed
            ],
            'metode_id' => [
                'required',
                'string',
                'exists:metode_bayar,metode_id'
            ],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'auto_complete' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate payment method is available for top-up
            if ($this->has('metode_id')) {
                $metodeBayar = MetodeBayar::where('metode_id', $this->metode_id)
                    ->first();
                
                if (!$metodeBayar) {
                    $validator->errors()->add(
                        'metode_id',
                        'Selected payment method is not available for top-up.'
                    );
                }
            }

            // Check daily top-up limit
            $mahasiswa = $this->user()->mahasiswa;
            $wallet = $mahasiswa ? $mahasiswa->wallet : null;
            if ($wallet) {
                $todayTopUps = \App\Models\WalletTransaction::where('wallet_id', $wallet->wallet_id)
                    ->where('tipe_transaksi', 'credit')
                    ->whereDate('created_at', today())
                    ->sum('amount');

                $dailyLimit = 10000000; // Rp 10,000,000 daily limit
                $remainingLimit = $dailyLimit - $todayTopUps;

                if ($this->amount > $remainingLimit) {
                    $validator->errors()->add(
                        'amount',
                        'Top-up amount exceeds daily limit. Remaining limit: Rp ' . number_format($remainingLimit, 0, ',', '.')
                    );
                }

                // Check monthly top-up limit
                $monthlyTopUps = \App\Models\WalletTransaction::where('wallet_id', $wallet->wallet_id)
                    ->where('tipe_transaksi', 'credit')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('amount');

                $monthlyLimit = 50000000; // Rp 50,000,000 monthly limit
                $remainingMonthlyLimit = $monthlyLimit - $monthlyTopUps;

                if ($this->amount > $remainingMonthlyLimit) {
                    $validator->errors()->add(
                        'amount',
                        'Top-up amount exceeds monthly limit. Remaining limit: Rp ' . number_format($remainingMonthlyLimit, 0, ',', '.')
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
        // Clean amount (remove formatting)
        if ($this->has('amount')) {
            $amount = preg_replace('/[^\d]/', '', $this->amount);
            $this->merge(['amount' => (int) $amount]);
        }

        // Set default values
        $this->merge([
            'auto_complete' => $this->auto_complete ?? false,
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
            'amount.required' => 'Top-up amount is required.',
            'amount.numeric' => 'Top-up amount must be a number.',
            'amount.min' => 'Minimum top-up amount is Rp 10,000.',
            'amount.max' => 'Maximum top-up amount is Rp 5,000,000.',
            'amount.regex' => 'Top-up amount must be a whole number.',
            'metode_id.required' => 'Payment method is required.',
            'metode_id.exists' => 'Selected payment method is invalid.',
            'keterangan.string' => 'Description must be a string.',
            'keterangan.max' => 'Description may not be greater than 255 characters.',
            'auto_complete.boolean' => 'Auto complete must be true or false.',
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
            'amount' => 'top-up amount',
            'metode_id' => 'payment method',
            'keterangan' => 'description',
            'auto_complete' => 'auto complete',
        ];
    }
}
