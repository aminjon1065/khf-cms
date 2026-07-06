<?php

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a view increments the counter and returns 204 without a token', function () {
    $news = News::factory()->create(['slug' => 'v', 'views' => 5]);

    $this->postJson('/api/v1/news/v/view')->assertNoContent();

    expect($news->refresh()->views)->toBe(6);
});

test('repeat views from the same ip and user agent are not double counted', function () {
    $news = News::factory()->create(['slug' => 'v', 'views' => 0]);

    $this->postJson('/api/v1/news/v/view', [], ['User-Agent' => 'Same'])->assertNoContent();
    $this->postJson('/api/v1/news/v/view', [], ['User-Agent' => 'Same'])->assertNoContent();

    expect($news->refresh()->views)->toBe(1);
});

test('a different user agent counts as a separate view', function () {
    $news = News::factory()->create(['slug' => 'v', 'views' => 0]);

    $this->postJson('/api/v1/news/v/view', [], ['User-Agent' => 'Agent-A'])->assertNoContent();
    $this->postJson('/api/v1/news/v/view', [], ['User-Agent' => 'Agent-B'])->assertNoContent();

    expect($news->refresh()->views)->toBe(2);
});

test('viewing missing or unpublished news returns 404', function () {
    News::factory()->draft()->create(['slug' => 'draft']);

    $this->postJson('/api/v1/news/draft/view')->assertNotFound();
    $this->postJson('/api/v1/news/missing/view')->assertNotFound();
});

test('the view endpoint is throttled to 10 requests per minute', function () {
    News::factory()->create(['slug' => 'v']);

    for ($i = 0; $i < 10; $i++) {
        $this->postJson('/api/v1/news/v/view', [], ['User-Agent' => "UA-{$i}"])->assertNoContent();
    }

    $this->postJson('/api/v1/news/v/view', [], ['User-Agent' => 'UA-last'])->assertStatus(429);
});

test('viewing does not create a revision', function () {
    $news = News::factory()->create(['slug' => 'v']);
    $before = $news->revisions()->count();

    $this->postJson('/api/v1/news/v/view')->assertNoContent();

    expect($news->revisions()->count())->toBe($before);
});
