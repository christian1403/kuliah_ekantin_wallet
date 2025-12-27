<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WalletTransaction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'wallet_transactions';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'transaction_id';

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
        'transaction_id',
        'wallet_id',
        'invoice',
        'amount',
        'tipe_transaksi',
        'waktu_transaksi',
        'deskripsi',
        'curr_balance',
        'after_balance',
        'items',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'curr_balance' => 'decimal:2',
        'after_balance' => 'decimal:2',
        'waktu_transaksi' => 'datetime',
        'items' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the wallet associated with the transaction.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'wallet_id');
    }

    /**
     * Get the detail pemasukan associated with the transaction.
     */
    public function detailPemasukan(): HasOne
    {
        return $this->hasOne(DetailPemasukan::class, 'transaction_id', 'transaction_id');
    }

    /**
     * Get the detail pengeluaran associated with the transaction.
     */
    public function detailPengeluaran(): HasOne
    {
        return $this->hasOne(DetailPengeluaran::class, 'transaction_id', 'transaction_id');
    }

    /**
     * Get the detail produk transactions for this transaction.
     */
    public function detailProdukTransactions(): HasMany
    {
        return $this->hasMany(DetailProdukTransaction::class, 'transaction_id', 'transaction_id');
    }

    /**
     * Get the products associated with this transaction.
     */
    public function produks()
    {
        return $this->belongsToMany(
            Produk::class,
            'detail_produk_transaction',
            'transaction_id',
            'produk_id'
        )->withPivot(['qty', 'harga'])->withTimestamps();
    }

    /**
     * Get the payment method through detail pemasukan.
     */
    public function paymentMethod()
    {
        return $this->hasOneThrough(
            MetodeBayar::class,
            DetailPemasukan::class,
            'transaction_id', // Foreign key on DetailPemasukan table
            'metode_id', // Foreign key on MetodeBayar table
            'transaction_id', // Local key on WalletTransaction table
            'metode_id' // Local key on DetailPemasukan table
        );
    }

    /**
     * Scope for filtering by transaction type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('tipe_transaksi', $type);
    }

    /**
     * Scope for filtering by wallet.
     */
    public function scopeByWallet($query, string $walletId)
    {
        return $query->where('wallet_id', $walletId);
    }

    /**
     * Scope for income transactions.
     */
    public function scopeIncome($query)
    {
        return $query->where('tipe_transaksi', 'pemasukan');
    }

    /**
     * Scope for expense transactions.
     */
    public function scopeExpense($query)
    {
        return $query->where('tipe_transaksi', 'pengeluaran');
    }

    /**
     * Get the formatted amount attribute.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Check if this is an income transaction.
     */
    public function isIncome(): bool
    {
        return $this->tipe_transaksi === 'pemasukan';
    }

    /**
     * Check if this is an expense transaction.
     */
    public function isExpense(): bool
    {
        return $this->tipe_transaksi === 'pengeluaran';
    }

    /**
     * Check if this transaction has products.
     */
    public function hasProducts(): bool
    {
        return $this->detailProdukTransactions()->exists();
    }

    /**
     * Get total items count in this transaction.
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->detailProdukTransactions()->sum('qty');
    }

    /**
     * Get total products value in this transaction.
     */
    public function getTotalProductValueAttribute(): float
    {
        return $this->detailProdukTransactions()->get()->sum('subtotal');
    }

    /**
     * Get formatted total product value.
     */
    public function getFormattedProductValueAttribute(): string
    {
        return 'Rp ' . number_format($this->total_product_value, 0, ',', '.');
    }
}
