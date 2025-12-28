# ID Generator Utility

This Laravel project includes a comprehensive ID Generator utility that can generate unique string IDs with a maximum of 255 characters for use in your models.

## Components

### 1. IdGenerator Utility Class (`App\Utils\IdGenerator`)
Core utility class with static methods for ID generation.

### 2. HasCustomId Trait (`App\Traits\HasCustomId`)
A trait that models can use to automatically generate IDs when creating new records.

### 3. Helper Functions (`app/Helpers/id_generator.php`)
Global helper functions for easy access to ID generation functionality.

## Usage Examples

### Basic Usage

```php
// Generate basic ID
$id = generate_id(); // "A1B2C3D4E5"

// Generate ID with prefix
$id = generate_id('USER'); // "USER_A1B2C3D4E5"

// Generate ID with custom length
$id = generate_id('PRODUCT', 8); // "PRODUCT_A1B2C3D4"

// Generate UUID-based ID
$id = generate_uuid_id('UUID'); // "UUID_550e8400e29b41d4a716446655440000"
```

### Model Integration with Trait

```php
<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Model;

class ExampleModel extends Model
{
    use HasCustomId;

    protected $primaryKey = 'example_id';
    protected $keyType = 'string';
    public $incrementing = false;

    // Optional: Configure ID generation
    protected $idConfig = [
        'prefix' => 'EXAMPLE',
        'length' => 12,
        'type' => 'alphanumeric', // alphanumeric, numeric, alpha, uuid
    ];
}

// Usage
$model = new ExampleModel();
$model->name = 'Test';
$model->save(); // ID will be automatically generated: "EXAMPLE_A1B2C3D4E5F6"
```

### Direct Class Usage

```php
use App\Utils\IdGenerator;

// Generate for specific model types
$mahasiswaId = IdGenerator::generateForModel('mahasiswa'); // "MHS_A1B2C3D4E5F6G7"
$merchantId = IdGenerator::generateForModel('merchant');   // "MERCHANT_A1B2C3D4"

// Generate timestamp-based ID
$orderId = IdGenerator::generateTimestamp('ORDER'); // "ORDER_20231228123456789ABC"

// Generate custom format
$customId = IdGenerator::generateCustom(['PART1', '2023', 'PART2']); // "PART1_2023_PART2"

// Generate unique ID (checks database)
$uniqueId = IdGenerator::generateUnique(
    User::class,        // Model class
    'user_id',          // Column name
    'USER',             // Prefix
    10,                 // Length
    'alphanumeric'      // Type
);
```

### Model-Specific Configurations

The utility includes predefined configurations for your models:

```php
// These will generate with predefined formats:
generate_model_id('mahasiswa');  // "MHS_XXXXXXXXXXXX" (12 chars)
generate_model_id('merchant');   // "MERCHANT_XXXXXXXXXX" (10 chars)
generate_model_id('kasir');      // "KASIR_XXXXXXXXXX" (10 chars)
generate_model_id('wallet');     // "WALLET_XXXXXXXXXXXX" (12 chars)
generate_model_id('transaction'); // "TXN_20231228123456789XXXXXX" (timestamp)
generate_model_id('produk');     // "PROD_XXXXXXXXXX" (10 chars)
```

### Advanced Usage

```php
// Manual ID generation in model
class CustomModel extends Model
{
    use HasCustomId;

    // Custom ID generation logic
    public function customIdGeneration(): string
    {
        return 'CUSTOM_' . now()->format('Ymd') . '_' . generate_id('', 6);
    }
}

// Check ID uniqueness
$model = new ExampleModel();
if ($model->isIdUnique()) {
    echo "ID is unique!";
}

// Regenerate ID
$model->regenerateId(true); // true = save after regenerating
```

## Configuration

You can customize the ID generation behavior by modifying the configuration in `config/id_generator.php`:

```php
return [
    'defaults' => [
        'length' => 10,
        'type' => 'alphanumeric',
        'max_attempts' => 100,
        'separator' => '_',
    ],
    
    'models' => [
        'your_model' => [
            'prefix' => 'PREFIX',
            'length' => 12,
            'type' => 'alphanumeric',
        ],
    ],
];
```

## ID Types

- `alphanumeric`: Uses 0-9 and A-Z characters
- `numeric`: Uses only 0-9 characters
- `alpha`: Uses only A-Z characters
- `uuid`: Uses UUID format (32 characters)

## Features

- ✅ Maximum 255 characters (database-safe)
- ✅ Automatic uniqueness checking
- ✅ Customizable prefixes and formats
- ✅ Model integration via trait
- ✅ Multiple ID generation strategies
- ✅ Global helper functions
- ✅ Configurable via config file
- ✅ Timestamp-based IDs
- ✅ UUID support
- ✅ Custom format support

## Integration with Your Existing Models

Update your existing models to use the trait:

```php
// Add to your existing models
use App\Traits\HasCustomId;

class Mahasiswa extends Model
{
    use HasFactory, HasCustomId;
    
    protected $idConfig = [
        'prefix' => 'MHS',
        'length' => 12,
        'type' => 'alphanumeric',
    ];
}
```