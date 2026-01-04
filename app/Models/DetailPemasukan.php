<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPemasukan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'detail_pemasukan';

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
        'metode_id',
        'transaction_id',
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
     * Get the payment method associated with the detail pemasukan.
     */
    public function metodeBayar(): BelongsTo
    {
        return $this->belongsTo(MetodeBayar::class, 'metode_id', 'metode_id');
    }

    /**
     * Get the wallet transaction associated with the detail pemasukan.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'transaction_id', 'transaction_id');
    }

    /**
     * Scope for filtering by payment method.
     */
    public function scopeByPaymentMethod($query, string $metodeId)
    {
        return $query->where('metode_id', $metodeId);
    }

    /**
     * Scope for filtering by transaction.
     */
    public function scopeByTransaction($query, string $transactionId)
    {
        return $query->where('transaction_id', $transactionId);
    }
}
