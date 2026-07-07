<?php

namespace App\Models;

use App\Services\RevalidationService;
use Database\Factories\SlideFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Translatable('title', 'category', 'source')]
class Slide extends Model implements HasMedia
{
    /** @use HasFactory<SlideFactory> */
    use HasFactory, HasTranslations, InteractsWithMedia, LogsActivity;

    /**
     * Slide image media collection; the API exposes it as the `image` ImageSet.
     */
    public const IMAGE_COLLECTION = 'image';

    protected $fillable = [
        'title',
        'category',
        'date',
        'source',
        'news_id',
        'image_media_asset_id',
        'sort',
        'active',
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

    protected static function booted(): void
    {
        static::saved(function (Slide $slide): void {
            // Revalidate when the slide is (or just stopped being) publicly shown.
            if ($slide->active || $slide->getRawOriginal('active')) {
                app(RevalidationService::class)->forSlides();
            }
        });

        static::deleted(function (Slide $slide): void {
            if ($slide->active) {
                app(RevalidationService::class)->forSlides();
            }
        });
    }

    /**
     * Optional link to a news item; the API exposes its slug as `newsSlug`.
     *
     * @return BelongsTo<News, $this>
     */
    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    /**
     * Slide image chosen from the reusable media library.
     *
     * @return BelongsTo<MediaAsset, $this>
     */
    public function imageAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'image_media_asset_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::IMAGE_COLLECTION)
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
     * no image. Shape matches API-CONTRACT ImageSet: thumb/card/hero/original.
     *
     * @return array{thumb: string, card: string, hero: string, original: string}|null
     */
    public function imageSet(): ?array
    {
        // Prefer the reusable library image; fall back to the legacy per-slide
        // spatie `image` collection so existing slides keep working.
        if ($this->imageAsset !== null && ($set = $this->imageAsset->imageSet()) !== null) {
            return $set;
        }

        $media = $this->getFirstMedia(self::IMAGE_COLLECTION);

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
     * Only active slides, in manual order (ToR §6.2, GET /home/slides).
     */
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
            ->logOnly(['title', 'category', 'date', 'source', 'news_id', 'sort', 'active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
