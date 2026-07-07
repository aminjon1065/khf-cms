<?php

use App\Support\CsvExporter;
use Symfony\Component\HttpFoundation\StreamedResponse;

function csvBody(StreamedResponse $response): string
{
    ob_start();
    $response->sendContent();

    return (string) ob_get_clean();
}

test('it streams a utf-8 csv with a bom and the given rows', function () {
    $response = CsvExporter::streamDownload('x.csv', ['A', 'B'], [['1', 'hi'], ['2', 'йо']]);

    $body = csvBody($response);

    expect($body)->toStartWith("\xEF\xBB\xBF")
        ->and($body)->toContain('A,B')
        ->and($body)->toContain('1,hi')
        ->and($body)->toContain('2,йо')
        ->and($response->headers->get('content-type'))->toContain('text/csv');
});

test('values with quotes and backslashes round-trip (rfc-4180)', function () {
    $value = 'path C:\\tmp and he said "привет"';

    $response = CsvExporter::streamDownload('x.csv', ['V'], [[$value]]);

    $body = substr(csvBody($response), 3); // drop the UTF-8 BOM
    $lines = array_values(array_filter(explode("\n", trim($body))));
    $row = str_getcsv($lines[1], ',', '"', '');

    expect($row[0])->toBe($value);
});

test('it neutralizes csv formula injection', function () {
    $response = CsvExporter::streamDownload('x.csv', ['V'], [['=SUM(A1)'], ['+1'], ['-1'], ['@cmd'], ['safe']]);

    $body = csvBody($response);

    expect($body)->toContain("'=SUM(A1)")
        ->and($body)->toContain("'+1")
        ->and($body)->toContain("'-1")
        ->and($body)->toContain("'@cmd")
        ->and($body)->toContain("\nsafe"); // ordinary value is not prefixed
});
