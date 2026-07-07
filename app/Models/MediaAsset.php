<?php

namespace App\Models;

use App\Enums\DocType;
use App\Services\RevalidationService;
use Database\Factories\MediaAssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A reusable media library item (WordPress-style): one uploaded file (image or
 * document) that any number of records can reference by id. Images get the same
 * thumb/card/hero conversions the API expects; documents are stored as-is.
 */
class MediaAsset extends Model implements HasMedia
{
    /** @use HasFactory<MediaAssetFactory> */
    use HasFactory, InteractsWithMedia, LogsActivity;

    /** The single-file collection holding the asset's file. */
    public const COLLECTION = 'file';

    /** Image MIME types the library accepts. */
    public const IMAGE_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /** Document MIME types the library accepts. */
    public const DOCUMENT_MIMES = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    /** All MIME types the library accepts (images + office documents). */
    public const ACCEPTED_MIMES = [...self::IMAGE_MIMES, ...self::DOCUMENT_MIMES];

    protected $fillable = [
        'name',
        'alt',
    ];

    /**
     * Create one library asset per uploaded file (WordPress-style bulk upload).
     * The asset name defaults to the file's base name; editors can rename later.
     *
     * @param  array<int, UploadedFile|null>  $files
     * @return Collection<int, static> the created assets
     */
    public static function createFromUploads(array $files): Collection
    {
        return collect($files)
            ->filter()
            ->map(function (UploadedFile $file): static {
                $asset = static::create([
                    'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'файл',
                ]);

                $asset->addMedia($file)->toMediaCollection(self::COLLECTION);

                return $asset;
            })
            ->values();
    }

    /**
     * Block deletion of an asset that is still referenced anywhere, so a shared
     * file can never be pulled out from under a post/document (which would blank
     * its image/link without revalidation). Editors must detach it first.
     */
    protected static function booted(): void
    {
        static::deleting(fn (MediaAsset $asset): bool => ! $asset->isInUse());
    }

    /**
     * Whether any News cover, News gallery, or Document currently references this
     * asset.
     */
    public function isInUse(): bool
    {
        return News::query()->where('cover_media_asset_id', $this->getKey())->exists()
            || Document::query()->where('media_asset_id', $this->getKey())->exists()
            || Slide::query()->where('image_media_asset_id', $this->getKey())->exists()
            || DB::table('news_gallery')->where('media_asset_id', $this->getKey())->exists();
    }

    /**
     * The asset's file changed (upload/replace), so every record that reuses it
     * now serves different bytes/URLs. Re-sync the derived document type/size and
     * flush the frontend caches for consuming published content (called from the
     * MediaHasBeenAddedEvent listener). The gallery is CMS-only, so it is skipped.
     */
    public function syncReferencesAfterFileChange(): void
    {
        Document::query()->where('media_asset_id', $this->getKey())->get()
            ->each(fn (Document $document) => $document->save());

        News::query()->where('cover_media_asset_id', $this->getKey())->get()
            ->each(function (News $news): void {
                if ($news->isPubliclyVisible()) {
                    app(RevalidationService::class)->forNews($news);
                }
            });

        if (Slide::query()->where('image_media_asset_id', $this->getKey())->where('active', true)->exists()) {
            app(RevalidationService::class)->forSlides();
        }
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes(self::ACCEPTED_MIMES);
    }

    /**
     * WebP conversions for images only; a non-image (document) registers none, so
     * nothing tries to rasterize a PDF. The small `thumb` is generated inline
     * (nonQueued) so library/picker previews appear immediately even without a
     * queue worker; the heavier card/hero stay queued (ToR §5.4 / §11).
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media !== null && ! str_starts_with((string) $media->mime_type, 'image/')) {
            return;
        }

        $this->addMediaConversion('thumb')->fit(Fit::Crop, 400, 300)->format('webp')->quality(82)->nonQueued();
        $this->addMediaConversion('card')->fit(Fit::Crop, 800, 600)->format('webp')->quality(82);
        $this->addMediaConversion('hero')->fit(Fit::Crop, 1920, 1080)->format('webp')->quality(82);
    }

    public function file(): ?Media
    {
        return $this->getFirstMedia(self::COLLECTION);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->file()?->mime_type, 'image/');
    }

    /**
     * 'image' | 'document' | 'empty' — used for filtering/labels in the library.
     */
    public function kind(): string
    {
        $media = $this->file();

        if ($media === null) {
            return 'empty';
        }

        return str_starts_with((string) $media->mime_type, 'image/') ? 'image' : 'document';
    }

    /**
     * Absolute conversion URLs (API ImageSet shape) for an image asset, else null.
     *
     * @return array{thumb: string, card: string, hero: string, original: string}|null
     */
    public function imageSet(): ?array
    {
        $media = $this->file();

        if ($media === null || ! str_starts_with((string) $media->mime_type, 'image/')) {
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
     * A small preview URL for images (null for documents). Uses the thumb once
     * generated, otherwise the original — so a preview always shows even before
     * conversions have run.
     */
    public function previewUrl(): ?string
    {
        $media = $this->file();

        if ($media === null || ! str_starts_with((string) $media->mime_type, 'image/')) {
            return null;
        }

        return $media->hasGeneratedConversion('thumb') ? $media->getFullUrl('thumb') : $media->getFullUrl();
    }

    public function fileUrl(): ?string
    {
        return $this->file()?->getFullUrl();
    }

    /**
     * DocType derived from the file's real MIME (documents only), else null.
     */
    public function docType(): ?DocType
    {
        $media = $this->file();

        if ($media === null || str_starts_with((string) $media->mime_type, 'image/')) {
            return null;
        }

        return DocType::fromMime((string) $media->mime_type)
            ?? DocType::fromExtension(pathinfo((string) $media->file_name, PATHINFO_EXTENSION));
    }

    public function humanSize(): string
    {
        return Document::formatBytes((int) ($this->file()?->size ?? 0));
    }

    /**
     * Whether any of the given asset id(s) is (not) an image — used by the picker
     * validation rule to keep image slots image-only and document slots doc-only.
     *
     * @param  mixed  $value  a single id or an array of ids
     */
    public static function selectionContainsImage(mixed $value, bool $image): bool
    {
        $ids = array_filter((array) $value);

        if ($ids === []) {
            return false;
        }

        return static::query()->whereKey($ids)->get()
            ->contains(fn (self $asset): bool => $asset->isImage() === $image);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'alt'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
