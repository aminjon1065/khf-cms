<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use App\Models\Concerns\GeneratesReference;
use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * A citizen emergency report submitted via POST /reports (docs/API-CONTRACT.md
 * §4). Reference is "ЧС-NNNNNN"; not exposed by any public GET.
 */
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use GeneratesReference, HasFactory, LogsActivity;

    protected $fillable = [
        'type',
        'region',
        'location',
        'description',
        'phone',
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
            'status' => SubmissionStatus::class,
        ];
    }

    protected function referencePrefix(): string
    {
        return 'ЧС';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'type', 'region'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
