<?php

namespace App\Models;

use App\Enums\RevalidationTag;
use App\Models\Concerns\RevalidatesContent;
use Database\Factories\ForumTopicFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * A forum topic (docs/API-CONTRACT.md §GET /forum). Exposes `slug` as the API
 * `id`; `category` references a ForumCategory slug; `last_activity` is a display
 * string. Pinned topics are returned first.
 */
#[Translatable('title', 'last_activity')]
class ForumTopic extends Model
{
    /** @use HasFactory<ForumTopicFactory> */
    use HasFactory, HasTranslations, LogsActivity, RevalidatesContent;

    protected $fillable = [
        'slug',
        'title',
        'category',
        'author',
        'replies',
        'views',
        'last_activity',
        'pinned',
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
            'replies' => 'integer',
            'views' => 'integer',
            'pinned' => 'boolean',
            'sort' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<ForumTopic>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * Pinned topics first, then manual order (docs/API-CONTRACT.md §GET /forum).
     *
     * @param  Builder<ForumTopic>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderByDesc('pinned')->orderBy('sort')->orderBy('id');
    }

    public function getFallbackLocale(): ?string
    {
        return 'tj';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['slug', 'title', 'category', 'author', 'replies', 'views', 'pinned', 'sort', 'active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * @return list<string>
     */
    protected function revalidationTags(): array
    {
        return [RevalidationTag::Forum->value];
    }
}
