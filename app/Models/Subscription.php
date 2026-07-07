<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use App\Models\Concerns\GeneratesReference;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * An alert subscription submitted via POST /subscriptions (docs/API-CONTRACT.md
 * §4). Reference is "SUB-NNNNNN"; not exposed by any public GET.
 */
class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use GeneratesReference, HasFactory, LogsActivity;

    protected $fillable = [
        'channel',
        'region',
        'categories',
        'contact',
        'status',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => SubmissionStatus::New->value,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'categories' => 'array',
            'status' => SubmissionStatus::class,
        ];
    }

    protected function referencePrefix(): string
    {
        return 'SUB';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'channel', 'region', 'contact'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
