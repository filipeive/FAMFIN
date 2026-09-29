<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'family_id',
        'category_id',
        'month',
        'amount',
        'alert_threshold',
    ];

    protected $casts = [
        'amount' => 'integer',
        'alert_threshold' => 'integer',
    ];

    // ── Relationships ──────────────────────────────

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ── Helpers ────────────────────────────────────

    public function getFormattedAmountAttribute(): string
    {
        return formatMoney($this->amount);
    }

    /**
     * Get total spent for this budget's category in this month.
     */
    public function getSpentAttribute(): int
    {
        return Transaction::where('category_id', $this->category_id)
            ->whereHas('account', fn ($q) => $q->where('family_id', $this->family_id))
            ->expenses()
            ->completed()
            ->forMonth($this->month)
            ->sum('amount');
    }

    public function getRemainingAttribute(): int
    {
        return $this->amount - $this->spent;
    }

    public function getPercentageUsedAttribute(): float
    {
        if ($this->amount === 0) {
            return 0;
        }

        return round(($this->spent / $this->amount) * 100, 1);
    }

    public function getIsOverBudgetAttribute(): bool
    {
        return $this->spent > $this->amount;
    }

    public function getIsAlertAttribute(): bool
    {
        return $this->percentage_used >= $this->alert_threshold;
    }
}
