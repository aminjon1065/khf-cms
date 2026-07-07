<?php

use App\Enums\SubmissionStatus;
use App\Mail\NewReportNotification;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

/**
 * @return array<string, string>
 */
function validReport(array $overrides = []): array
{
    return array_merge([
        'type' => 'Сӯхтор',
        'region' => 'Душанбе',
        'location' => 'кӯчаи Рӯдакӣ 1',
        'description' => 'Дым из окна третьего этажа.',
        'phone' => '+992 37 221-12-12',
    ], $overrides);
}

test('a valid report is stored and acknowledged without a data wrapper', function () {
    Mail::fake();

    $response = $this->postJson('/api/v1/reports', validReport());

    $response->assertOk()
        ->assertJson(['ok' => true])
        ->assertJsonStructure(['ok', 'reference'])
        ->assertJsonMissingPath('data');

    expect($response->json('reference'))->toMatch('/^ЧС-\d{6}$/u');

    $report = Report::query()->firstOrFail();

    expect($report->status)->toBe(SubmissionStatus::New)
        ->and($report->phone)->toBe('+992372211212') // normalized
        ->and($report->reference)->toBe($response->json('reference'));
});

test('reports receive distinct sequential references', function () {
    Mail::fake();

    $first = $this->postJson('/api/v1/reports', validReport())->json('reference');
    $second = $this->postJson('/api/v1/reports', validReport())->json('reference');

    expect($first)->not->toBe($second)
        ->and($first)->toMatch('/^ЧС-\d{6}$/u')
        ->and($second)->toMatch('/^ЧС-\d{6}$/u');
});

test('the form requires all fields', function () {
    $this->postJson('/api/v1/reports', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['type', 'region', 'location', 'description', 'phone']);

    expect(Report::query()->count())->toBe(0);
});

test('a filled honeypot is rejected', function () {
    $this->postJson('/api/v1/reports', validReport(['website' => 'http://spam.example']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['website']);

    expect(Report::query()->count())->toBe(0);
});

test('reports are throttled to 5 per minute per ip', function () {
    Mail::fake();

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/reports', validReport())->assertOk();
    }

    $this->postJson('/api/v1/reports', validReport())->assertStatus(429);
});

test('the report form throttle bucket is isolated from the other forms', function () {
    Mail::fake();

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/reports', validReport())->assertOk();
    }
    $this->postJson('/api/v1/reports', validReport())->assertStatus(429);

    // A different form keeps its own budget.
    $this->postJson('/api/v1/contact', [
        'name' => 'Тест',
        'email' => 'test@example.tj',
        'subject' => 'Тема',
        'message' => 'Текст обращения.',
    ])->assertOk();
});

test('the view beacon does not consume the report form budget', function () {
    Mail::fake();

    // Hammer the public view beacon (its own bucket). The target need not exist —
    // the throttle middleware runs before the controller.
    for ($i = 0; $i < 6; $i++) {
        $this->postJson('/api/v1/news/nonexistent/view');
    }

    // The safety-critical emergency form is still reachable.
    $this->postJson('/api/v1/reports', validReport())->assertOk();
});

test('a new report notifies the configured on-duty officers', function () {
    Mail::fake();
    config(['khf.duty.emails' => 'duty@khf.tj, ops@khf.tj']);

    $this->postJson('/api/v1/reports', validReport())->assertOk();

    Mail::assertQueued(
        NewReportNotification::class,
        fn (NewReportNotification $mail): bool => $mail->hasTo('duty@khf.tj') && $mail->hasTo('ops@khf.tj'),
    );
});

test('no notification is sent when no on-duty officers are configured', function () {
    Mail::fake();
    config(['khf.duty.emails' => '']);

    $this->postJson('/api/v1/reports', validReport())->assertOk();

    Mail::assertNothingQueued();
});
