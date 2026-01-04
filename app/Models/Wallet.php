<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasCustomId;

class Wallet extends Model
{
    use HasFactory, HasCustomId;

    /**
     * The table associated with the model.
     */
    protected $table = 'wallet';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'wallet_id';

    /**
     * The "type" of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;
    /**
     * ID configuration for automatic generation
     */
    protected $idConfig = [
        'prefix' => '',
        'length' => 32,
        'type' => 'uuid',
        'without_prefix' => true,
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'wallet_id',
        'mahasiswa_id',
        'balance',
        'balance_settled',
        'pin',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'balance' => 'decimal:2',
        'balance_settled' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'pin',
    ];

    /**
     * Get the mahasiswa that owns the wallet.
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'mahasiswa_id');
    }

    /**
     * Get the transactions associated with the wallet.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id', 'wallet_id');
    }

    /**
     * Get income transactions.
     */
    public function incomeTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id', 'wallet_id')
                    ->where('tipe_transaksi', 'pemasukan');
    }

    /**
     * Get expense transactions.
     */
    public function expenseTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id', 'wallet_id')
                    ->where('tipe_transaksi', 'pengeluaran');
    }
}
