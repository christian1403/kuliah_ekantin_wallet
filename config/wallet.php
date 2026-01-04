<?php
    
    return [
        /*
        |--------------------------------------------------------------------------
        | Wallet Configuration
        |--------------------------------------------------------------------------
        |
        | This file is for storing the configuration settings related to the
        | digital wallet functionality for mahasiswa (students). You can
        | adjust these settings as needed for your application.
        |
        */

        // Default wallet PIN salt for hashing
        'pin_salt' => env('WALLET_PIN_SALT', 'default_salt_value'),
        'default_pin' => env('WALLET_DEFAULT_PIN', '123456'),

        // Invoice prefix
        'list_invoice_prefix' => [
            'create_wallet' => 'CREATEWALLET-',
            'top_up' => 'TOPUP-',
            'transfer' => 'TRANSFER-',
            'merchant_payment' => 'MERCHANTPAY-',
        ],

        // Transaction types
        'transaction_types' => [
            'credit' => 'credit',
            'debit' => 'debit',
            'initial' => 'initial',
        ],
        'status_types' => [
            'pending' => 'pending',
            'completed' => 'completed',
            'failed' => 'failed',
        ],
    ];