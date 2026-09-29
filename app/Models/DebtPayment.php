<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebtPayment extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'debt_id',
        'user_id',
        'amount',
        'payment_date',
        'note',
    ];

    protected $casts = [
        'amount' => 'integer',
        'payment_date' => 'date',
    ];

    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
