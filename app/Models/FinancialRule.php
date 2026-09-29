<?php

namespace App\Models;

use App\Enums\FinancialRuleType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialRule extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'family_id',
        'type',
        'name',
        'description',
        'percentage',
        'is_active',
        'apply_on_income',
        'config',
        'sort_order',
    ];

    protected $casts = [
        'type' => FinancialRuleType::class,
        'percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'apply_on_income' => 'boolean',
        'config' => 'array',
        'sort_order' => 'integer',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * Calculate the deduction amount from a gross value.
     * All values in centavos.
     */
    public function calculate(int $grossAmount): int
    {
        return (int) floor($grossAmount * ($this->percentage / 100));
    }
}
