<?php

use App\Enums\NewsStatus;
use App\Jobs\SendRevalidationRequest;
use App\Models\News;
use App\Models\Slide;
use App\Services\RevalidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------- job behaviour

test('the revalidation job retries three times with backoff', function () {
    $job = new SendRevalidationRequest(['news']);

    expect($job->tries)->toBe(3)
        ->and($job->backoff())->toBe([10, 30, 60]);
});

test('the job posts tags to the configured webhook with the secret header', function () {
    config()->set('khf.revalidate.frontend_url', 'https://front.test');
    config()->set('khf.revalidate.secret', 's3cret');
    Http::fake(['*' => Http::response(['ok' => true])]);

    (new SendRevalidationRequest(['news', 'home']))->handle();

    Http::assertSent(fn ($request) => $request->url() === 'https://front.test/api/revalidate'
        && $request->method() === 'POST'
        && $request->header('x-revalidate-secret')[0] === 's3cret'
        && $request['tags'] === ['news', 'home']
        && $request['paths'] === []);
});

test('the job is a no-op when the webhook is unconfigured', function () {
    config()->set('khf.revalidate.frontend_url', null);
    config()->set('khf.revalidate.secret', null);
    Http::fake();

    (new SendRevalidationRequest(['news']))->handle();

    Http::assertNothingSent();
});

test('the job throws on a failed response so the queue retries', function () {
    config()->set('khf.revalidate.frontend_url', 'https://front.test');
    config()->set('khf.revalidate.secret', 's3cret');
    Http::fake(['*' => Http::response('nope', 500)]);

    expect(fn () => (new SendRevalidationRequest(['news']))->handle())
        ->toThrow(RequestException::class);
});

// ------------------------------------------------------------- service tag maps

test('forNews dispatches news, news slug and home tags', function () {
    Queue::fake();
    $news = new News(['slug' => 'my-slug']);

    app(RevalidationService::class)->forNews($news);

    Queue::assertPushed(SendRevalidationRequest::class, fn (SendRevalidationRequest $job) => $job->tags === ['news', 'news:my-slug', 'home']);
});

test('forSlides dispatches home and news tags', function () {
    Queue::fake();

    app(RevalidationService::class)->forSlides();

    Queue::assertPushed(SendRevalidationRequest::class, fn (SendRevalidationRequest $job) => $job->tags === ['home', 'news']);
});

test('flushAll dispatches every fixed tag from the contract', function () {
    Queue::fake();

    app(RevalidationService::class)->flushAll();

    Queue::assertPushed(SendRevalidationRequest::class, fn (SendRevalidationRequest $job) => $job->tags === ['news', 'home', 'documents', 'structure', 'activities', 'regions', 'contacts', 'forum']);
});

test('revalidate skips an empty tag set', function () {
    Queue::fake();

    app(RevalidationService::class)->revalidate([]);

    Queue::assertNothingPushed();
});

// ------------------------------------------------------------- model hook gates

test('publishing news dispatches a revalidation with its tags', function () {
    Queue::fake();

    News::factory()->create(['slug' => 'fresh']);

    Queue::assertPushed(SendRevalidationRequest::class, fn (SendRevalidationRequest $job) => $job->tags === ['news', 'news:fresh', 'home']);
});

test('saving a draft does not dispatch a revalidation', function () {
    Queue::fake();

    News::factory()->draft()->create();

    Queue::assertNotPushed(SendRevalidationRequest::class);
});

test('archiving a published news dispatches a revalidation', function () {
    $news = News::factory()->create(['slug' => 'arch']);

    Queue::fake(); // ignore the create-time dispatch

    $news->update(['status' => NewsStatus::Archived]);

    Queue::assertPushed(SendRevalidationRequest::class, fn (SendRevalidationRequest $job) => $job->tags === ['news', 'news:arch', 'home']);
});

test('deleting a published news dispatches a revalidation', function () {
    $news = News::factory()->create(['slug' => 'del']);

    Queue::fake();

    $news->delete();

    Queue::assertPushed(SendRevalidationRequest::class);
});

test('deleting a draft does not dispatch a revalidation', function () {
    $news = News::factory()->draft()->create();

    Queue::fake();

    $news->delete();

    Queue::assertNotPushed(SendRevalidationRequest::class);
});

test('saving an active slide dispatches the slide revalidation', function () {
    Queue::fake();

    Slide::factory()->create();

    Queue::assertPushed(SendRevalidationRequest::class, fn (SendRevalidationRequest $job) => $job->tags === ['home', 'news']);
});

test('saving an inactive slide does not dispatch a revalidation', function () {
    Queue::fake();

    Slide::factory()->inactive()->create();

    Queue::assertNotPushed(SendRevalidationRequest::class);
});
