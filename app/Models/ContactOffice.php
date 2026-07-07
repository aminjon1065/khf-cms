<?php

namespace App\Models;

use App\Enums\RevalidationTag;
use App\Models\Concerns\RevalidatesContent;
use App\Services\RevalidationService;
use Database\Factories\ContactOfficeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * A contact office (docs/API-CONTRACT.md §GET /contacts). The single office
 * flagged `is_head` is served as `headOffice`; the rest are `offices`.
 * Uniqueness of the head office is enforced on save (see booted()).
 */
#[Translatable('region', 'address', 'hours')]
class ContactOffice extends Model
{
    /** @use HasFactory<ContactOfficeFactory> */
    use HasFactory, HasTranslations, LogsActivity, RevalidatesContent;

    protected $fillable = [
        'region',
        'address',
        'hours',
        'phone',
        'email',
        'is_head',
        'sort',
        'active',
    ];

    /**
     * Mirror the DB default so a record created without an explicit `active`
     * still reports active=true in-memory (needed by the revalidation gate).
     *
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
            'is_head' => 'boolean',
            'sort' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * Guarantee a single head office: whenever a record is saved as head, demote
     * every other head row. The mass update bypasses model events, so the demoted
     * record cannot revalidate on its own. When the demotion removes a currently
     * public (active) head AND this save would not itself flush the cache — i.e.
     * the record is being saved inactive, so its RevalidatesContent gate stays
     * closed — flush the `contacts` tag here so the frontend never serves a stale
     * head office. An active save already flushes via its own saved event.
     */
    protected static function booted(): void
    {
        static::saving(function (ContactOffice $office): void {
            if (! $office->is_head) {
                return;
            }

            $others = static::query()
                ->where('is_head', true)
                ->when($office->exists, fn (Builder $query): Builder => $query->whereKeyNot($office->getKey()));

            $demotesPublicHead = ! $office->active
                && (clone $others)->where('active', true)->exists();

            $others->update(['is_head' => false]);

            if ($demotesPublicHead) {
                app(RevalidationService::class)->revalidate([RevalidationTag::Contacts->value]);
            }
        });
    }

    /**
     * @param  Builder<ContactOffice>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * @param  Builder<ContactOffice>  $query
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
            ->logOnly(['region', 'phone', 'email', 'is_head', 'sort', 'active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * @return list<string>
     */
    protected function revalidationTags(): array
    {
        return [RevalidationTag::Contacts->value];
    }
}
