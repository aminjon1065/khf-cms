<?php

use App\Enums\NewsStatus;
use App\Models\News;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the command publishes only scheduled news that are due', function () {
    $due = News::factory()->scheduled()->create(['published_at' => now()->subMinute()]);
    $future = News::factory()->scheduled()->create(['published_at' => now()->addDay()]);
    $draft = News::factory()->draft()->create();

    $this->artisan('news:publish-scheduled')->assertSuccessful();

    expect($due->refresh()->status)->toBe(NewsStatus::Published)
        ->and($future->refresh()->status)->toBe(NewsStatus::Scheduled)
        ->and($draft->refresh()->status)->toBe(NewsStatus::Draft);
});

test('the command is scheduled to run every minute', function () {
    $events = collect(app(Schedule::class)->events())
        ->filter(fn ($event): bool => str_contains((string) $event->command, 'news:publish-scheduled'));

    expect($events)->toHaveCount(1)
        ->and($events->first()->expression)->toBe('* * * * *');
});
