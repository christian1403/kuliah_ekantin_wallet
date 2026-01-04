<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | ID Generator Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the ID Generator utility.
    | You can customize default behaviors and model-specific settings here.
    |
    */

    'defaults' => [
        'length' => 32,
        'type' => 'uuid', // alphanumeric, numeric, alpha, uuid
        'max_attempts' => 100,
        'separator' => '_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Model-Specific ID Configurations
    |--------------------------------------------------------------------------
    |
    | Define custom ID generation rules for specific models.
    | These will be used when calling IdGenerator::generateForModel()
    |
    */

    'models' => [
        'mahasiswa' => [
            'prefix' => 'MHS',
            'length' => 12,
            'type' => 'alphanumeric',
        ],
        'merchant' => [
            'prefix' => 'MERCHANT',
            'length' => 10,
            'type' => 'alphanumeric',
        ],
        'kasir' => [
            'prefix' => 'KASIR',
            'length' => 10,
            'type' => 'alphanumeric',
        ],
        'wallet' => [
            'prefix' => 'WALLET',
            'length' => 12,
            'type' => 'alphanumeric',
        ],
        'transaction' => [
            'prefix' => 'TXN',
            'type' => 'timestamp',
            'include_milliseconds' => true,
        ],
        'produk' => [
            'prefix' => 'PROD',
            'length' => 10,
            'type' => 'alphanumeric',
        ],
        'metode' => [
            'prefix' => 'METHOD',
            'length' => 8,
            'type' => 'alphanumeric',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ID Types Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the character sets for different ID types
    |
    */

    'character_sets' => [
        'alphanumeric' => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'numeric' => '0123456789',
        'alpha' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'lowercase' => 'abcdefghijklmnopqrstuvwxyz',
        'mixed' => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
    ],

];