<?php

namespace App\Models;

use App\Enums\NewsStatus;
use App\Services\BodySanitizer;
use App\Services\NewsRevisionService;
use App\Services\RevalidationService;
use App\Services\Transliterator;
use Database\Factories\NewsFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Translatable('title', 'excerpt', 'body', 'seo_title', 'seo_description')]
class News extends Model implements HasMedia
{
    /** @use HasFactory<NewsFactory> */
    use HasFactory, HasTranslations, InteractsWithMedia, LogsActivity;

    /**
     * Cover image media collection; the API exposes it as the `image` ImageSet.
     */
    public const COVER_COLLECTION = 'cover';

    protected $table = 'news';

    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'region_id',
        'excerpt',
        'body',
        'author',
        'status',
        'published_at',
        'views',
        'seo_title',
        'seo_description',
        'og_image',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => NewsStatus::class,
            'published_at' => 'datetime',
            'views' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (News $news): void {
            if (blank($news->slug)) {
                $news->slug = static::makeUniqueSlug(
                    Transliterator::slug((string) $news->getTranslation('title', 'tj'))
                );
            }
        });

        static::saving(function (News $news): void {
            if (! $news->isDirty('body')) {
                return;
            }

            $sanitizer = app(BodySanitizer::class);

            foreach ($news->getTranslations('body') as $locale => $html) {
                $news->setTranslation('body', $locale, $sanitizer->clean($html));
            }
        });

        static::saved(function (News $news): void {
            app(NewsRevisionService::class)->record($news);
        });

        static::saved(function (News $news): void {
            if ($news->shouldTriggerRevalidation()) {
                app(RevalidationService::class)->forNews($news);
            }
        });

        static::deleted(function (News $news): void {
            if ($news->isPubliclyVisible()) {
                app(RevalidationService::class)->forNews($news);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * @return HasMany<NewsRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(NewsRevision::class)->latest('id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::COVER_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * Conversions the frontend expects as an ImageSet. Left queued (the default)
     * so heavy WebP encoding runs on the queue worker, per ToR §5.4 / §11.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit(Fit::Crop, 400, 300)->format('webp')->quality(82);
        $this->addMediaConversion('card')->fit(Fit::Crop, 800, 600)->format('webp')->quality(82);
        $this->addMediaConversion('hero')->fit(Fit::Crop, 1920, 1080)->format('webp')->quality(82);
    }

    /**
     * Absolute conversion URLs for the API `image` field, or null when there is
     * no cover. Shape matches API-CONTRACT ImageSet: thumb/card/hero/original.
     *
     * @return array{thumb: string, card: string, hero: string, original: string}|null
     */
    public function imageSet(): ?array
    {
        $media = $this->getFirstMedia(self::COVER_COLLECTION);

        if ($media === null) {
            return null;
        }

        return [
            'thumb' => $media->getFullUrl('thumb'),
            'card' => $media->getFullUrl('card'),
            'hero' => $media->getFullUrl('hero'),
            'original' => $media->getFullUrl(),
        ];
    }

    /**
     * Only news visible to the public API: published and not scheduled in the
     * future (ToR §5.3).
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', NewsStatus::Published)
            ->where('published_at', '<=', now());
    }

    /**
     * Whether this item is currently visible through the public API.
     */
    public function isPubliclyVisible(): bool
    {
        return $this->status === NewsStatus::Published
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    /**
     * Whether a save should revalidate the frontend: it affects public output
     * only if the item is public now or was published before the change (e.g.
     * an archive). Draft autosaves are intentionally skipped.
     */
    public function shouldTriggerRevalidation(): bool
    {
        return $this->isPubliclyVisible()
            || $this->getRawOriginal('status') === NewsStatus::Published->value;
    }

    public function getFallbackLocale(): ?string
    {
        return 'tj';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'slug', 'category_id', 'region_id', 'status', 'published_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected static function makeUniqueSlug(string $base): string
    {
        $base = $base !== '' ? $base : 'news';
        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
