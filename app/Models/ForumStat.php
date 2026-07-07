<?php

namespace App\Models;

use App\Services\RevalidationService;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Singleton holding the forum's global community stats (docs/API-CONTRACT.md
 * §GET /forum). All four values are editor-set display strings, e.g. "8 420".
 * Use ForumStat::current().
 */
class ForumStat extends Model
{
    use LogsActivity;

    protected $fillable = [
        'members',
        'topics',
        'posts',
        'online',
    ];

    protected static function booted(): void
    {
        static::saved(function (): void {
            app(RevalidationService::class)->forForum();
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
            ->logOnly(['members', 'topics', 'posts', 'online'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
