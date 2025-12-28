<?php

namespace App\Traits;

use App\Utils\IdGenerator;

trait HasCustomId
{
    /**
     * Boot the trait
     */
    protected static function bootHasCustomId()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = $model->generateId();
            }
        });
    }
    
    /**
     * Generate ID for the model
     *
     * @return string
     */
    public function generateId(): string
    {
        $idConfig = $this->getIdConfig();
        
        if (method_exists($this, 'customIdGeneration')) {
            return $this->customIdGeneration();
        }
        
        return IdGenerator::generateUnique(
            static::class,
            $this->getKeyName(),
            $idConfig['prefix'] ?? '',
            $idConfig['length'] ?? 10,
            $idConfig['type'] ?? 'alphanumeric',
            $idConfig['max_attempts'] ?? 100
        );
    }
    
    /**
     * Get ID configuration for the model
     *
     * Override this method in your model to customize ID generation
     *
     * @return array
     */
    protected function getIdConfig(): array
    {
        // Default configuration
        $config = [
            'prefix' => '',
            'length' => 10,
            'type' => 'alphanumeric',
            'max_attempts' => 100,
            'without_prefix' => false,
        ];
        
        // Check if model has idConfig property
        if (property_exists($this, 'idConfig')) {
            $config = array_merge($config, $this->idConfig);
        }
        
        // Auto-detect based on table name if no prefix is set and without_prefix is false
        if (empty($config['prefix']) && !$config['without_prefix']) {
            $config['prefix'] = $this->getDefaultPrefix();
        }
        
        return $config;
    }
    
    /**
     * Get default prefix based on table name or model name
     *
     * @return string
     */
    protected function getDefaultPrefix(): string
    {
        // Try to get from table name first
        $tableName = $this->getTable();
        
        // Convert table name to prefix
        $prefix = strtoupper($tableName);
        
        // Remove common suffixes
        $prefix = preg_replace('/(_table|s)$/i', '', $prefix);
        
        // Limit prefix length to keep within 255 char limit
        return substr($prefix, 0, 20);
    }
    
    /**
     * Generate ID using model type
     *
     * @return string
     */
    public function generateModelId(): string
    {
        $modelName = class_basename($this);
        return IdGenerator::generateForModel($modelName);
    }
    
    /**
     * Regenerate ID (useful for testing or manual regeneration)
     *
     * @param bool $save Whether to save the model after regenerating
     * @return string
     */
    public function regenerateId(bool $save = false): string
    {
        $this->{$this->getKeyName()} = $this->generateId();
        
        if ($save) {
            $this->save();
        }
        
        return $this->{$this->getKeyName()};
    }
    
    /**
     * Check if the current ID is unique
     *
     * @return bool
     */
    public function isIdUnique(): bool
    {
        $query = static::where($this->getKeyName(), $this->{$this->getKeyName()});
        
        if ($this->exists) {
            $query->where($this->getKeyName(), '!=', $this->getOriginal($this->getKeyName()));
        }
        
        return !$query->exists();
    }
}