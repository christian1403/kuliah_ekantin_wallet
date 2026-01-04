<?php

use App\Utils\IdGenerator;

if (!function_exists('generate_id')) {
    /**
     * Generate a unique string ID
     *
     * @param string $prefix
     * @param int $length
     * @param string $type
     * @return string
     */
    function generate_id(string $prefix = '', int $length = 10, string $type = 'alphanumeric'): string
    {
        return IdGenerator::generate($prefix, $length, $type);
    }
}

if (!function_exists('generate_uuid_id')) {
    /**
     * Generate a UUID-based ID
     *
     * @param string $prefix
     * @return string
     */
    function generate_uuid_id(string $prefix = ''): string
    {
        return IdGenerator::generateUuid($prefix);
    }
}

if (!function_exists('generate_model_id')) {
    /**
     * Generate ID for specific model type
     *
     * @param string $modelType
     * @return string
     */
    function generate_model_id(string $modelType): string
    {
        return IdGenerator::generateForModel($modelType);
    }
}

if (!function_exists('generate_unique_id')) {
    /**
     * Generate unique ID that doesn't exist in database
     *
     * @param string $modelClass
     * @param string $column
     * @param string $prefix
     * @param int $length
     * @param string $type
     * @return string
     */
    function generate_unique_id(
        string $modelClass,
        string $column,
        string $prefix = '',
        int $length = 10,
        string $type = 'alphanumeric'
    ): string {
        return IdGenerator::generateUnique($modelClass, $column, $prefix, $length, $type);
    }
}