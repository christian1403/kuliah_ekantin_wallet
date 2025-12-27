<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Merchant extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'merchants';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'merchant_id';

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
        'merchant_id',
        'kode_merchant',
        'nama',
        'email',
        'alamat',
        'no_hp',
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
     * Get the kasir associated with the merchant.
     */
    public function kasir(): HasMany
    {
        return $this->hasMany(Kasir::class, 'merchant_id', 'merchant_id');
    }

    /**
     * Get the products associated with the merchant.
     */
    public function produks(): HasMany
    {
        return $this->hasMany(Produk::class, 'merchant_id', 'merchant_id');
    }

    /**
     * Get the detail pengeluaran associated with the merchant.
     */
    public function detailPengeluaran(): HasMany
    {
        return $this->hasMany(DetailPengeluaran::class, 'merchant_id', 'merchant_id');
    }

    /**
     * Scope for filtering by merchant code.
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('kode_merchant', $code);
    }

    /**
     * Scope for filtering by email.
     */
    public function scopeByEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Get the display name attribute.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->kode_merchant ? "{$this->nama} ({$this->kode_merchant})" : $this->nama;
    }

    /**
     * Check if the merchant has any kasir.
     */
    public function hasKasir(): bool
    {
        return $this->kasir()->exists();
    }

    /**
     * Check if the merchant has any products.
     */
    public function hasProducts(): bool
    {
        return $this->produks()->exists();
    }

    /**
     * Get active kasir count.
     */
    public function getActiveKasirCountAttribute(): int
    {
        return $this->kasir()->count();
    }

    /**
     * Get total products count.
     */
    public function getTotalProductsCountAttribute(): int
    {
        return $this->produks()->count();
    }

    /**
     * Get product transactions through products.
     */
    public function productTransactions()
    {
        return $this->hasManyThrough(
            DetailProdukTransaction::class,
            Produk::class,
            'merchant_id', // Foreign key on Produk table
            'produk_id', // Foreign key on DetailProdukTransaction table
            'merchant_id', // Local key on Merchant table
            'produk_id' // Local key on Produk table
        );
    }

    /**
     * Get total revenue from product sales.
     */
    public function getTotalRevenueAttribute(): float
    {
        return $this->productTransactions()->get()->sum('subtotal');
    }

    /**
     * Get formatted total revenue.
     */
    public function getFormattedRevenueAttribute(): string
    {
        return 'Rp ' . number_format($this->total_revenue, 0, ',', '.');
    }
}
