<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * A message submitted via the contact form POST /contact (docs/API-CONTRACT.md
 * §4). No reference is issued; not exposed by any public GET.
 */
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'name', 'subject'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
