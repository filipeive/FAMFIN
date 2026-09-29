<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsGoal extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'family_id',
        'name',
        'description',
        'target_amount',
        'current_amount',
        'deadline',
        'color',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'target_amount' => 'integer',
        'current_amount' => 'integer',
        'deadline' => 'date',
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(SavingsContribution::class);
    }

    // ── Helpers ────────────────────────────────────

    public function getProgressPercentageAttribute(): float
    {
        if ($this->target_amount === 0) {
            return 0;
        }

        return round(($this->current_amount / $this->target_amount) * 100, 1);
    }

    public function getRemainingAttribute(): int
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->current_amount >= $this->target_amount;
    }

    public function getMonthlyNeededAttribute(): ?int
    {
        if (!$this->deadline || $this->is_completed) {
            return null;
        }

        $monthsLeft = now()->diffInMonths($this->deadline);

        if ($monthsLeft <= 0) {
            return $this->remaining;
        }

        return (int) ceil($this->remaining / $monthsLeft);
    }
}
