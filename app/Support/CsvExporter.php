<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a UTF-8 CSV download (with a BOM so Excel reads Cyrillic correctly)
 * for the «Обращения» inboxes. Every cell is guarded against CSV formula
 * injection because the data includes untrusted, citizen-submitted text.
 */
class CsvExporter
{
    /**
     * @param  list<string>  $headers
     * @param  iterable<int, list<string|int|float|null>>  $rows
     */
    public static function streamDownload(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'wb');

            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            // escape: '' → RFC-4180 output (embedded quotes doubled, no backslash
            // escaping) so Excel/readers round-trip citizen text correctly; also
            // silences the PHP 8.5 fputcsv() $escape deprecation.
            fputcsv($handle, array_map(self::guard(...), $headers), escape: '');

            foreach ($rows as $row) {
                fputcsv($handle, array_map(self::guard(...), $row), escape: '');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Neutralize CSV formula injection by prefixing a single quote to any value
     * that a spreadsheet would treat as a formula (=, +, -, @, or a control
     * char), per Filament's export security guidance.
     */
    private static function guard(string|int|float|null $value): string
    {
        $value = (string) $value;

        if ($value !== '' && preg_match("/^[=+\-@\t\r]/", $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
