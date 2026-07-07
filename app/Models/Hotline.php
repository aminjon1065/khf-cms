<?php

namespace App\Models;

use App\Enums\RevalidationTag;
use App\Models\Concerns\RevalidatesContent;
use Database\Factories\HotlineFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * An emergency hotline (docs/API-CONTRACT.md §GET /contacts). `is_primary` is
 * sent as the optional API `primary` flag.
 */
#[Translatable('label', 'note')]
class Hotline extends Model
{
    /** @use HasFactory<HotlineFactory> */
    use HasFactory, HasTranslations, LogsActivity, RevalidatesContent;

    protected $fillable = [
        'number',
        'label',
        'note',
        'is_primary',
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
            'is_primary' => 'boolean',
            'sort' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Hotline>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * @param  Builder<Hotline>  $query
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
            ->logOnly(['number', 'label', 'is_primary', 'sort', 'active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * @return list<string>
     */
    protected function revalidationTags(): array
    {
        return [RevalidationTag::Contacts->value];
    }
}
