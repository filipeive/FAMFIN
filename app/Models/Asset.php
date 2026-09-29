<?php

namespace App\Models;

use App\Enums\AssetType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'family_id',
        'name',
        'type',
        'description',
        'current_value',
        'purchase_value',
        'purchase_date',
    ];

    protected $casts = [
        'type' => AssetType::class,
        'current_value' => 'integer',
        'purchase_value' => 'integer',
        'purchase_date' => 'date',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function getAppreciationAttribute(): ?int
    {
        if (!$this->purchase_value) {
            return null;
        }

        return $this->current_value - $this->purchase_value;
    }
}
