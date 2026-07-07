<?php

namespace App\Models;

use App\Services\RevalidationService;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Singleton holding the map's global "monitoring" stat string (the other map
 * stats are computed from MapRegion rows). Use MapSetting::current().
 */
class MapSetting extends Model
{
    use LogsActivity;

    protected $fillable = [
        'monitoring',
    ];

    protected static function booted(): void
    {
        static::saved(function (): void {
            app(RevalidationService::class)->forRegions();
        });
    }

    /**
     * The single settings row, or a fresh unsaved instance. Never persists on
     * read (a GET must be side-effect-free).
     */
    public static function current(): self
    {
        return static::query()->firstOrNew([]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['monitoring'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
