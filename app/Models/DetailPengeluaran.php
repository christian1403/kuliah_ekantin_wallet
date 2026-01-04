<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPengeluaran extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'detail_pengeluaran';

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
        'merchant_id',
        'transaction_id',
        'kasir_id',
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
     * Get the merchant associated with the detail pengeluaran.
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'merchant_id');
    }

    /**
     * Get the transaction associated with the detail pengeluaran.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'transaction_id', 'transaction_id');
    }

    /**
     * Get the kasir associated with the detail pengeluaran.
     */
    public function kasir(): BelongsTo
    {
        return $this->belongsTo(Kasir::class, 'kasir_id', 'kasir_id');
    }

    /**
     * Scope for filtering by merchant.
     */
    public function scopeByMerchant($query, string $merchantId)
    {
        return $query->where('merchant_id', $merchantId);
    }

    /**
     * Scope for filtering by kasir.
     */
    public function scopeByKasir($query, string $kasirId)
    {
        return $query->where('kasir_id', $kasirId);
    }
}
