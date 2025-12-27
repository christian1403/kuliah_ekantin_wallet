<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailProdukTransaction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'detail_produk_transaction';

    /**
     * Indicates if the model should use timestamps.
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'produk_id',
        'transaction_id',
        'qty',
        'harga',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'qty' => 'integer',
        'harga' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the product associated with the detail.
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'produk_id');
    }

    /**
     * Get the transaction associated with the detail.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'transaction_id', 'transaction_id');
    }

    /**
     * Scope for filtering by product.
     */
    public function scopeByProduct($query, string $produkId)
    {
        return $query->where('produk_id', $produkId);
    }

    /**
     * Scope for filtering by transaction.
     */
    public function scopeByTransaction($query, string $transactionId)
    {
        return $query->where('transaction_id', $transactionId);
    }

    /**
     * Scope for filtering by minimum quantity.
     */
    public function scopeMinQuantity($query, int $minQty)
    {
        return $query->where('qty', '>=', $minQty);
    }

    /**
     * Get the subtotal for this detail (qty * harga).
     */
    public function getSubtotalAttribute(): float
    {
        return $this->qty * $this->harga;
    }

    /**
     * Get the formatted subtotal attribute.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    /**
     * Get the formatted price attribute.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }

    /**
     * Check if quantity is available in product stock.
     */
    public function isQuantityAvailable(): bool
    {
        return $this->produk && $this->produk->stok >= $this->qty;
    }
}
