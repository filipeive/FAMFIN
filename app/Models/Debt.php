<?php

namespace App\Models;

use App\Enums\DebtStatus;
use App\Enums\DebtType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debt extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'family_id',
        'person_name',
        'type',
        'description',
        'original_amount',
        'remaining_amount',
        'interest_rate',
        'interest_type',
        'start_date',
        'due_date',
        'status',
    ];

    protected $casts = [
        'type' => DebtType::class,
        'status' => DebtStatus::class,
        'original_amount' => 'integer',
        'remaining_amount' => 'integer',
        'interest_rate' => 'decimal:2',
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    // ── Relationships ──────────────────────────────

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }

    // ── Scopes ─────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', DebtStatus::Active);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', DebtStatus::Overdue);
    }

    // ── Helpers ────────────────────────────────────

    public function getTotalPaidAttribute(): int
    {
        return $this->payments()->sum('amount');
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->original_amount === 0) {
            return 0;
        }

        return round(($this->total_paid / $this->original_amount) * 100, 1);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && $this->status === DebtStatus::Active;
    }
}
