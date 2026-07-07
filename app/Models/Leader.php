<?php

namespace App\Models;

use App\Enums\RevalidationTag;
use App\Models\Concerns\RevalidatesContent;
use Database\Factories\LeaderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * A member of the committee leadership (docs/API-CONTRACT.md §GET /structure).
 */
#[Translatable('name', 'role', 'rank', 'bio')]
class Leader extends Model
{
    /** @use HasFactory<LeaderFactory> */
    use HasFactory, HasTranslations, LogsActivity, RevalidatesContent;

    protected $fillable = [
        'name',
        'role',
        'rank',
        'bio',
        'sort',
        'active',
    ];

    /**
     * Mirror the DB default so a record created without an explicit `active`
     * still reports active=true in-memory (needed by the revalidation gate).
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Leader>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * @param  Builder<Leader>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort')->orderBy('id');
    }

    public function getFallbackLocale(): ?string
    {
        return 'tj';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'role', 'rank', 'sort', 'active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * @return list<string>
     */
    protected function revalidationTags(): array
    {
        return [RevalidationTag::Structure->value];
    }
}
