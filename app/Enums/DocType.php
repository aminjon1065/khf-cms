<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Document file type, derived from the uploaded file and exposed verbatim
 * (uppercased) in the API `type` field (docs/API-CONTRACT.md §GET /documents).
 */
enum DocType: string implements HasLabel
{
    case Pdf = 'pdf';
    case Docx = 'docx';
    case Xlsx = 'xlsx';

    public function getLabel(): string
    {
        return $this->apiValue();
    }

    /**
     * Uppercase form sent to the frontend (PDF|DOCX|XLSX).
     */
    public function apiValue(): string
    {
        return mb_strtoupper($this->value);
    }

    /**
     * Map a file extension to a document type, or null if unsupported.
     */
    public static function fromExtension(string $extension): ?self
    {
        return self::tryFrom(mb_strtolower($extension));
    }

    /**
     * Map a content-based MIME type to a document type, or null if unsupported.
     * Preferred over the filename extension so a mislabeled file cannot be
     * exposed under the wrong type (ToR §5.4).
     */
    public static function fromMime(string $mime): ?self
    {
        return match ($mime) {
            'application/pdf' => self::Pdf,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => self::Docx,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => self::Xlsx,
            default => null,
        };
    }
}
