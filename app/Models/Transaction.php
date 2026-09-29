<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\RecurrencePattern;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'account_id',
        'category_id',
        'user_id',
        'transfer_account_id',
        'type',
        'status',
        'amount',
        'description',
        'notes',
        'transaction_date',
        'is_recurring',
        'recurrence_pattern',
        'recurrence_end_date',
        'parent_transaction_id',
    ];

    protected $casts = [
        'type' => TransactionType::class,
        'status' => TransactionStatus::class,
        'recurrence_pattern' => RecurrencePattern::class,
        'amount' => 'integer',
        'transaction_date' => 'date',
        'recurrence_end_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transferAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'transfer_account_id');
    }

    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'parent_transaction_id');
    }

    public function childTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'parent_transaction_id');
    }

    // ── Scopes ─────────────────────────────────────

    public function scopeIncomes($query)
    {
        return $query->where('type', TransactionType::Income);
    }

    public function scopeExpenses($query)
    {
        return $query->where('type', TransactionType::Expense);
    }

    public function scopeTransfers($query)
    {
        return $query->where('type', TransactionType::Transfer);
    }

    public function scopeForMonth($query, string $month)
    {
        $start = \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $end = \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();
        return $query->whereBetween('transaction_date', [$start, $end]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', TransactionStatus::Completed);
    }

    // ── Accessors ──────────────────────────────────

    public function getFormattedAmountAttribute(): string
    {
        return formatMoney($this->amount);
    }

    public function getIsIncomeAttribute(): bool
    {
        return $this->type === TransactionType::Income;
    }

    public function getIsExpenseAttribute(): bool
    {
        return $this->type === TransactionType::Expense;
    }

    public function getIsTransferAttribute(): bool
    {
        return $this->type === TransactionType::Transfer;
    }
}
