<?php

namespace App\Models;

use App\Enums\RevalidationTag;
use App\Models\Concerns\RevalidatesContent;
use Database\Factories\ForumCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * A forum category (docs/API-CONTRACT.md §GET /forum). Exposes `slug` as the API
 * `id`; `topics`/`posts` are editor-set display counts, `icon` a lucide name.
 */
#[Translatable('title', 'description')]
class ForumCategory extends Model
{
    /** @use HasFactory<ForumCategoryFactory> */
    use HasFactory, HasTranslations, LogsActivity, RevalidatesContent;

    protected $fillable = [
        'slug',
        'title',
        'description',
        'topics',
        'posts',
        'icon',
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
            'topics' => 'integer',
            'posts' => 'integer',
            'sort' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<ForumCategory>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * @param  Builder<ForumCategory>  $query
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
            ->logOnly(['slug', 'title', 'topics', 'posts', 'icon', 'sort', 'active'])
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
