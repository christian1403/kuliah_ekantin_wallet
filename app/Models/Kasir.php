<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasCustomId;

class Kasir extends Model
{
    use HasFactory, HasCustomId;

    /**
     * The table associated with the model.
     */
    protected $table = 'kasir';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'kasir_id';

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
        'kasir_id',
        'merchant_id',
        'nama',
        'email',
        'no_hp',
        'user_id',
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
     * Get the user that owns the kasir.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the merchant that owns the kasir.
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'merchant_id');
    }

    /**
     * Get the detail pengeluaran handled by this kasir.
     */
    public function detailPengeluaran(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DetailPengeluaran::class, 'kasir_id', 'kasir_id');
    }

    /**
     * Scope for filtering by merchant.
     */
    public function scopeByMerchant($query, string $merchantId)
    {
        return $query->where('merchant_id', $merchantId);
    }

    /**
     * Scope for filtering by email.
     */
    public function scopeByEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Get the display name with merchant info.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->merchant ? "{$this->nama} - {$this->merchant->nama}" : $this->nama;
    }

    /**
     * Check if the kasir is linked to a user account.
     */
    public function hasUserAccount(): bool
    {
        return !is_null($this->user_id);
    }
}
