<?php

namespace App\Models;

use App\Enums\DocType;
use App\Enums\DocumentCategory;
use App\Services\RevalidationService;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Translatable('title')]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory, HasTranslations, LogsActivity;

    /** Disk holding uploaded document files. */
    public const DISK = 'public';

    protected $fillable = [
        'title',
        'category',
        'number',
        'document_date',
        'type',
        'size',
        'file_path',
        'media_asset_id',
        'sort',
        'is_active',
    ];

    /**
     * Mirror the DB default so a document created without an explicit
     * `is_active` still reports active=true in-memory (revalidation gate).
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => DocumentCategory::class,
            'type' => DocType::class,
            'document_date' => 'date',
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Document $document): void {
            $document->syncFileMetadata();
        });

        static::saved(function (Document $document): void {
            if ($document->shouldTriggerRevalidation()) {
                app(RevalidationService::class)->forDocuments();
            }
        });

        static::deleted(function (Document $document): void {
            if ($document->is_active) {
                app(RevalidationService::class)->forDocuments();
            }
        });
    }

    /**
     * A reusable library document, when one is linked instead of a direct upload.
     */
    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    /**
     * Derive `type` and `size` from the actual file so the API values always
     * match it (docs/API-CONTRACT.md §documents). A linked library asset wins
     * over the legacy per-document `file_path`.
     */
    protected function syncFileMetadata(): void
    {
        if (filled($this->media_asset_id)) {
            $asset = $this->mediaAsset()->first();

            if ($asset !== null) {
                if (($type = $asset->docType()) !== null) {
                    $this->type = $type;
                }

                $this->size = $asset->humanSize();

                return;
            }
        }

        if (! $this->isDirty('file_path') || blank($this->file_path)) {
            return;
        }

        $disk = Storage::disk(self::DISK);

        if (! $disk->exists($this->file_path)) {
            return;
        }

        // Prefer content-based MIME over the filename extension so a mislabeled
        // file cannot be exposed under the wrong type (ToR §5.4).
        $type = DocType::fromMime((string) $disk->mimeType($this->file_path))
            ?? DocType::fromExtension(pathinfo($this->file_path, PATHINFO_EXTENSION));

        if ($type !== null) {
            $this->type = $type;
        }

        $this->size = self::formatBytes($disk->size($this->file_path));
    }

    /**
     * Absolute URL to the file, or null when none is attached. A linked library
     * asset wins over the legacy per-document `file_path`.
     */
    public function fileUrl(): ?string
    {
        if (filled($this->media_asset_id)) {
            return $this->mediaAsset?->fileUrl();
        }

        return blank($this->file_path) ? null : Storage::disk(self::DISK)->url($this->file_path);
    }

    /**
     * Documents visible to the public API.
     *
     * @param  Builder<Document>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Whether a save affects public output: the document is active now, or was
     * active before the change (e.g. it was just hidden). A fresh insert has no
     * prior state (getOriginal reflects the $attributes default), so it only
     * counts as public when active now.
     */
    public function shouldTriggerRevalidation(): bool
    {
        $wasActive = ! $this->wasRecentlyCreated && (bool) $this->getOriginal('is_active');

        return $this->is_active || $wasActive;
    }

    public function getFallbackLocale(): ?string
    {
        return 'tj';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'category', 'number', 'document_date', 'type', 'is_active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * Human-readable byte size using Russian units (Б/КБ/МБ/ГБ). Bytes and KB
     * are shown as integers, larger units with one decimal.
     */
    public static function formatBytes(int $bytes): string
    {
        $units = ['Б', 'КБ', 'МБ', 'ГБ'];
        $power = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);
        $value = $bytes / (1024 ** $power);

        $formatted = $power <= 1
            ? (string) (int) round($value)
            : number_format($value, 1, ',', ' ');

        return $formatted.' '.$units[$power];
    }
}
