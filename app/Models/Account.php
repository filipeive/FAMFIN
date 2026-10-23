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
        'parent_id',
        'name',
        'type',
        'balance',
        'initial_balance',
        'description',
        'color',
        'icon',
        'is_active',
        'include_in_total',
        'is_allowance',
        'assigned_to_name',
        'allowance_monthly_amount',
    ];

    protected $casts = [
        'type' => AccountType::class,
        'balance' => 'integer',
        'initial_balance' => 'integer',
        'is_active' => 'boolean',
        'include_in_total' => 'boolean',
        'is_allowance' => 'boolean',
        'allowance_monthly_amount' => 'integer',
    ];

    // ── Relationships ──────────────────────────────

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function subAccounts(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ── Scopes ─────────────────────────────────────

    public function scopeMainAccounts($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeAllowances($query)
    {
        return $query->where('is_allowance', true);
    }

    // ── Accessors ──────────────────────────────────

    public function getFormattedBalanceAttribute(): string
    {
        return formatMoney($this->balance, $this->family->currency ?? 'MZN');
    }
}
