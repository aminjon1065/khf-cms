<?php

namespace App\Models;

use App\Models\Concerns\RevalidatesStructure;
use Database\Factories\RegionalOfficeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * A regional office of the committee (docs/API-CONTRACT.md §GET /structure).
 */
#[Translatable('region', 'head', 'address')]
class RegionalOffice extends Model
{
    /** @use HasFactory<RegionalOfficeFactory> */
    use HasFactory, HasTranslations, LogsActivity, RevalidatesStructure;

    protected $fillable = [
        'region',
        'head',
        'phone',
        'address',
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
     * @param  Builder<RegionalOffice>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * @param  Builder<RegionalOffice>  $query
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
            ->logOnly(['region', 'head', 'phone', 'sort', 'active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
