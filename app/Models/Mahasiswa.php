<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Mahasiswa extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'mahasiswa';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'mahasiswa_id';

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
        'mahasiswa_id',
        'npm',
        'email',
        'nama',
        'alamat',
        'tanggal_lahir',
        'jenis_kelamin',
        'no_hp',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_lahir' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the mahasiswa.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the wallet associated with the mahasiswa.
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'mahasiswa_id', 'mahasiswa_id');
    }

    /**
     * Scope for filtering by gender.
     */
    public function scopeByGender($query, string $gender)
    {
        return $query->where('jenis_kelamin', $gender);
    }

    /**
     * Scope for filtering by NPM.
     */
    public function scopeByNpm($query, string $npm)
    {
        return $query->where('npm', $npm);
    }

    /**
     * Get the full name attribute.
     */
    public function getFullNameAttribute(): string
    {
        return $this->nama;
    }

    /**
     * Get the gender display attribute.
     */
    public function getGenderDisplayAttribute(): string
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    /**
     * Get the formatted birth date attribute.
     */
    public function getFormattedBirthDateAttribute(): ?string
    {
        return $this->tanggal_lahir ? $this->tanggal_lahir->format('d F Y') : null;
    }

    /**
     * Check if the mahasiswa has a wallet.
     */
    public function hasWallet(): bool
    {
        return $this->wallet()->exists();
    }

    /**
     * Check if the mahasiswa is linked to a user account.
     */
    public function hasUserAccount(): bool
    {
        return !is_null($this->user_id);
    }
}
