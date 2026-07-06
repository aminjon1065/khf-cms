<?php

namespace App\Models;

use App\Services\RevalidationService;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * Singleton holding the home page's President quote and site stats (ToR §6.9).
 * Use HomeSetting::current() to fetch (or lazily create) the single row.
 */
#[Translatable('president_role', 'president_quote')]
class HomeSetting extends Model
{
    use HasTranslations, LogsActivity;

    protected $fillable = [
        'president_name',
        'president_role',
        'president_quote',
        'president_href',
        'stats_today',
        'stats_month',
        'stats_rescued',
        'stats_reaction',
    ];

    protected static function booted(): void
    {
        static::saved(function (): void {
            app(RevalidationService::class)->forHome();
        });
    }

    /**
     * The single settings row, or a fresh unsaved instance. Never persists on
     * read (a GET must be side-effect-free); the admin page/seeder save it.
     */
    public static function current(): self
    {
        return static::query()->firstOrNew([]);
    }

    public function getFallbackLocale(): ?string
    {
        return 'tj';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'president_name', 'president_role', 'president_quote', 'president_href',
                'stats_today', 'stats_month', 'stats_rescued', 'stats_reaction',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
