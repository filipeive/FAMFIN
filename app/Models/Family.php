<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'currency',
        'timezone',
        'locale',
    ];

    // ── Relationships ──────────────────────────────

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->through('accounts');
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function savingsGoals(): HasMany
    {
        return $this->hasMany(SavingsGoal::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function financialRules(): HasMany
    {
        return $this->hasMany(FinancialRule::class);
    }

    // ── Helpers ────────────────────────────────────

    /**
     * Get active financial rules, ordered for sequential application.
     */
    public function activeRules()
    {
        return $this->financialRules()
            ->where('is_active', true)
            ->orderBy('sort_order');
    }
}
