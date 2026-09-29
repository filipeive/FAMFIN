<?php

namespace App\Models;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'family_id',
        'name',
        'type',
        'balance',
        'initial_balance',
        'description',
        'color',
        'icon',
        'is_active',
        'include_in_total',
    ];

    protected $casts = [
        'type' => AccountType::class,
        'balance' => 'integer',
        'initial_balance' => 'integer',
        'is_active' => 'boolean',
        'include_in_total' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ── Accessors ──────────────────────────────────

    public function getFormattedBalanceAttribute(): string
    {
        return formatMoney($this->balance, $this->family->currency ?? 'MZN');
    }
}
