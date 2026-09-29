<?php

namespace App\Models;

use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'family_id',
        'parent_id',
        'name',
        'type',
        'icon',
        'color',
        'sort_order',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'type' => CategoryType::class,
        'sort_order' => 'integer',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    // ── Helpers ────────────────────────────────────

    public function isParent(): bool
    {
        return $this->parent_id === null;
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function getFullNameAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->name . ' > ' . $this->name;
        }

        return $this->name;
    }
}
