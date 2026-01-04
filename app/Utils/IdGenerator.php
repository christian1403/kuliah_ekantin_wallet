<?php

namespace App\Utils;

use Illuminate\Support\Str;

class IdGenerator
{
    /**
     * Generate a unique string ID with custom prefix
     *
     * @param string $prefix The prefix for the ID (e.g., 'MAHASISWA', 'MERCHANT')
     * @param int $length The total length of the random part (default: 10)
     * @param string $type The type of ID generation ('alphanumeric', 'numeric', 'uuid')
     * @return string
     */
    public static function generate(string $prefix = '', int $length = 10, string $type = 'alphanumeric'): string
    {
        $maxLength = 255;
        
        // Calculate available length for the random part
        $availableLength = $maxLength - strlen($prefix) - 1; // -1 for separator
        
        if ($length > $availableLength) {
            $length = $availableLength;
        }
        
        $randomPart = match ($type) {
            'uuid' => str_replace('-', '', Str::uuid()->toString()),
            'numeric' => static::generateNumeric($length),
            'alphanumeric' => static::generateAlphanumeric($length),
            'alpha' => static::generateAlpha($length),
            default => static::generateAlphanumeric($length),
        };
        
        if (empty($prefix)) {
            return substr($randomPart, 0, min(strlen($randomPart), $maxLength));
        }
        
        $separator = '_';
        $fullId = $prefix . $separator . $randomPart;
        
        // Ensure the total length doesn't exceed 255 characters
        return substr($fullId, 0, $maxLength);
    }
    
    /**
     * Generate a UUID-based ID with prefix
     *
     * @param string $prefix
     * @return string
     */
    public static function generateUuid(string $prefix = ''): string
    {
        return static::generate($prefix, 32, 'uuid');
    }
    
    /**
     * Generate a timestamp-based ID with prefix
     *
     * @param string $prefix
     * @param bool $includeMilliseconds
     * @return string
     */
    public static function generateTimestamp(string $prefix = '', bool $includeMilliseconds = true): string
    {
        $timestamp = now()->format('YmdHis');
        
        if ($includeMilliseconds) {
            $timestamp .= now()->format('u');
        }
        
        $randomSuffix = static::generateAlphanumeric(6);
        $timePart = $timestamp . $randomSuffix;
        
        if (empty($prefix)) {
            return substr($timePart, 0, 255);
        }
        
        $fullId = $prefix . '_' . $timePart;
        return substr($fullId, 0, 255);
    }
    
    /**
     * Generate a custom format ID
     *
     * @param array $parts Array of parts to combine
     * @param string $separator
     * @return string
     */
    public static function generateCustom(array $parts, string $separator = '_'): string
    {
        $id = implode($separator, $parts);
        return substr($id, 0, 255);
    }
    
    /**
     * Generate alphanumeric string
     *
     * @param int $length
     * @return string
     */
    private static function generateAlphanumeric(int $length): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = '';
        
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $result;
    }
    
    /**
     * Generate numeric string
     *
     * @param int $length
     * @return string
     */
    private static function generateNumeric(int $length): string
    {
        $result = '';
        
        for ($i = 0; $i < $length; $i++) {
            $result .= random_int(0, 9);
        }
        
        return $result;
    }
    
    /**
     * Generate alphabetic string
     *
     * @param int $length
     * @return string
     */
    private static function generateAlpha(int $length): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = '';
        
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $result;
    }
    
    /**
     * Generate ID for specific model types with predefined formats
     *
     * @param string $modelType
     * @return string
     */
    public static function generateForModel(string $modelType): string
    {
        return match (strtolower($modelType)) {
            'mahasiswa' => static::generate('MHS', 12, 'alphanumeric'),
            'merchant' => static::generate('MERCHANT', 10, 'alphanumeric'),
            'kasir' => static::generate('KASIR', 10, 'alphanumeric'),
            'wallet' => static::generate('WALLET', 12, 'alphanumeric'),
            'transaction' => static::generateTimestamp('TXN'),
            'produk' => static::generate('PROD', 10, 'alphanumeric'),
            'metode' => static::generate('METHOD', 8, 'alphanumeric'),
            default => static::generate('ID', 12, 'alphanumeric'),
        };
    }
    
    /**
     * Check if an ID exists in a given model
     *
     * @param string $modelClass
     * @param string $column
     * @param string $id
     * @return bool
     */
    public static function exists(string $modelClass, string $column, string $id): bool
    {
        if (!class_exists($modelClass)) {
            return false;
        }
        
        return $modelClass::where($column, $id)->exists();
    }
    
    /**
     * Generate unique ID that doesn't exist in database
     *
     * @param string $modelClass
     * @param string $column
     * @param string $prefix
     * @param int $length
     * @param string $type
     * @param int $maxAttempts
     * @return string
     */
    public static function generateUnique(
        string $modelClass, 
        string $column, 
        string $prefix = '', 
        int $length = 10, 
        string $type = 'alphanumeric',
        int $maxAttempts = 100
    ): string {
        $attempts = 0;
        
        do {
            $id = static::generate($prefix, $length, $type);
            $attempts++;
            
            if ($attempts >= $maxAttempts) {
                // Fallback to UUID if max attempts reached
                $id = static::generateUuid($prefix);
                break;
            }
        } while (static::exists($modelClass, $column, $id));
        
        return $id;
    }
}