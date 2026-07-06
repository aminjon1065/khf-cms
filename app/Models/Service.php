<?php

namespace App\Models;

use App\Services\RevalidationService;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * Home "quick service" button (ToR §6.9). The API exposes `key` as the
 * contract's `id` (e.g. "112", "report").
 */
#[Translatable('title', 'subtitle')]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, HasTranslations, LogsActivity;

    protected $fillable = [
        'key',
        'title',
        'subtitle',
        'tel',
        'route',
        'primary',
        'sort',
        'active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'primary' => 'boolean',
            'sort' => 'integer',
            'active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (Service $service): void {
            if ($service->active || $service->getRawOriginal('active')) {
                app(RevalidationService::class)->forHome();
            }
        });

        static::deleted(function (Service $service): void {
            if ($service->active) {
                app(RevalidationService::class)->forHome();
            }
        });
    }

    public function scopeActiveOrdered(Builder $query): void
    {
        $query->where('active', true)
            ->orderBy('sort')
            ->orderBy('id');
    }

    public function getFallbackLocale(): ?string
    {
        return 'tj';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['key', 'title', 'subtitle', 'tel', 'route', 'primary', 'sort', 'active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
