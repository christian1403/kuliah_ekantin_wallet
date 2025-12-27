<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetodeBayar extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'metode_bayar';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'metode_id';

    /**
     * The "type" of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'metode_id',
        'nama',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the detail pemasukan associated with this payment method.
     */
    public function detailPemasukan(): HasMany
    {
        return $this->hasMany(DetailPemasukan::class, 'metode_id', 'metode_id');
    }

    /**
     * Get transactions through detail pemasukan.
     */
    public function transactions()
    {
        return $this->hasManyThrough(
            WalletTransaction::class,
            DetailPemasukan::class,
            'metode_id', // Foreign key on DetailPemasukan table
            'transaction_id', // Foreign key on WalletTransaction table
            'metode_id', // Local key on MetodeBayar table
            'transaction_id' // Local key on DetailPemasukan table
        );
    }

    /**
     * Scope for filtering by payment method name.
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('nama', 'like', "%{$name}%");
    }

    /**
     * Get the display name attribute.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->nama ?? 'Unknown Payment Method';
    }

    /**
     * Check if this payment method has been used in transactions.
     */
    public function hasTransactions(): bool
    {
        return $this->detailPemasukan()->exists();
    }
}
