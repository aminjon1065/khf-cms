<?php

namespace App\Models;

use App\Enums\RevalidationTag;
use App\Enums\RiskLevel;
use App\Models\Concerns\RevalidatesContent;
use Database\Factories\MapRegionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * An operational map region (docs/API-CONTRACT.md §GET /regions). Exposes
 * `slug` as the API `id`; `risk` is sent as its backing value.
 */
#[Translatable('name', 'center', 'note')]
class MapRegion extends Model
{
    /** @use HasFactory<MapRegionFactory> */
    use HasFactory, HasTranslations, LogsActivity, RevalidatesContent;

    protected $fillable = [
        'slug',
        'name',
        'center',
        'note',
        'risk',
        'active_incidents',
        'stations',
        'sort',
        'active',
    ];

    /**
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
            'risk' => RiskLevel::class,
            'active_incidents' => 'integer',
            'stations' => 'integer',
            'sort' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<MapRegion>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * @param  Builder<MapRegion>  $query
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
            ->logOnly(['slug', 'name', 'risk', 'active_incidents', 'stations', 'sort', 'active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * @return list<string>
     */
    protected function revalidationTags(): array
    {
        return [RevalidationTag::Regions->value];
    }
}
