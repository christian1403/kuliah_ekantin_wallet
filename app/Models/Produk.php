<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produk extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'produks';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'produk_id';

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
        'produk_id',
        'merchant_id',
        'kode_produk',
        'nama',
        'harga',
        'stok',
        'gambar',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'harga' => 'decimal:2',
        'stok' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the merchant that owns the product.
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'merchant_id');
    }

    /**
     * Get the detail produk transactions for this product.
     */
    public function detailProdukTransactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DetailProdukTransaction::class, 'produk_id', 'produk_id');
    }

    /**
     * Get the transactions that include this product.
     */
    public function transactions()
    {
        return $this->belongsToMany(
            WalletTransaction::class,
            'detail_produk_transaction',
            'produk_id',
            'transaction_id'
        )->withPivot(['qty', 'harga'])->withTimestamps();
    }

    /**
     * Scope for filtering by merchant.
     */
    public function scopeByMerchant($query, string $merchantId)
    {
        return $query->where('merchant_id', $merchantId);
    }

    /**
     * Scope for filtering by product code.
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('kode_produk', $code);
    }

    /**
     * Scope for products in stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('stok', '>', 0);
    }

    /**
     * Scope for products by name search.
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('nama', 'like', "%{$name}%");
    }

    /**
     * Get the formatted price attribute.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stok > 0;
    }

    /**
     * Check if quantity is available.
     */
    public function hasStock(int $quantity): bool
    {
        return $this->stok >= $quantity;
    }

    /**
     * Get total quantity sold.
     */
    public function getTotalSoldAttribute(): int
    {
        return $this->detailProdukTransactions()->sum('qty');
    }
}
