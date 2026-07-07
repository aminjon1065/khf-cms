<?php

use App\Jobs\SendRevalidationRequest;
use App\Models\ForumCategory;
use App\Models\ForumStat;
use App\Models\ForumTopic;
use App\Models\User;
use Database\Seeders\ForumSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function actingAsForumApi(): void
{
    Sanctum::actingAs(User::factory()->create());
}

test('forum endpoint requires a bearer token', function () {
    ForumCategory::factory()->create();

    $this->getJson('/api/v1/forum')->assertUnauthorized();
});

test('returns the contract shape with categories, topics and stats', function () {
    actingAsForumApi();
    ForumCategory::factory()->create();
    ForumTopic::factory()->create();

    $this->getJson('/api/v1/forum')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'categories' => [['id', 'title', 'description', 'topics', 'posts', 'icon']],
                'topics' => [['id', 'title', 'category', 'author', 'replies', 'views', 'lastActivity', 'pinned']],
                'stats' => ['members', 'topics', 'posts', 'online'],
            ],
        ]);
});

test('category and topic ids are their slugs and counts are numbers', function () {
    actingAsForumApi();
    ForumCategory::factory()->create(['slug' => 'general', 'topics' => 124, 'posts' => 980, 'sort' => 1]);
    ForumTopic::factory()->pinned()->create(['slug' => 't1', 'replies' => 42, 'views' => 1820, 'sort' => 1]);

    $data = $this->getJson('/api/v1/forum')->assertOk()->json('data');

    expect($data['categories'][0]['id'])->toBe('general')
        ->and($data['categories'][0]['topics'])->toBe(124)
        ->and($data['categories'][0]['posts'])->toBe(980)
        ->and($data['topics'][0]['id'])->toBe('t1')
        ->and($data['topics'][0]['replies'])->toBe(42)
        ->and($data['topics'][0]['pinned'])->toBeTrue();
});

test('forum stats come from the singleton and default to empty strings when unset', function () {
    actingAsForumApi();
    ForumCategory::factory()->create();

    expect($this->getJson('/api/v1/forum')->assertOk()->json('data.stats'))
        ->toBe(['members' => '', 'topics' => '', 'posts' => '', 'online' => '']);
});

test('forum stats are the stored display strings', function () {
    actingAsForumApi();
    ForumCategory::factory()->create();
    ForumStat::query()->create(['members' => '8 420', 'topics' => '470', 'posts' => '3 512', 'online' => '63']);

    expect($this->getJson('/api/v1/forum')->assertOk()->json('data.stats'))
        ->toBe(['members' => '8 420', 'topics' => '470', 'posts' => '3 512', 'online' => '63']);
});

test('only active entries are returned, pinned topics first then manual order', function () {
    actingAsForumApi();
    ForumCategory::factory()->create(['slug' => 'catb', 'sort' => 2]);
    ForumCategory::factory()->create(['slug' => 'cata', 'sort' => 1]);
    ForumCategory::factory()->inactive()->create(['slug' => 'catx', 'sort' => 0]);

    ForumTopic::factory()->create(['slug' => 'b', 'sort' => 2]);
    ForumTopic::factory()->create(['slug' => 'a', 'sort' => 1]);
    ForumTopic::factory()->pinned()->create(['slug' => 'p', 'sort' => 3]);
    ForumTopic::factory()->inactive()->create(['slug' => 'x', 'sort' => 0]);

    $data = $this->getJson('/api/v1/forum')->assertOk()->json('data');

    expect(collect($data['categories'])->pluck('id')->all())->toBe(['cata', 'catb'])
        ->and(collect($data['topics'])->pluck('id')->all())->toBe(['p', 'a', 'b']);
});

test('translatable fields resolve to the request locale', function () {
    actingAsForumApi();
    ForumCategory::factory()->create(['title' => ['tj' => 'ТҶ', 'ru' => 'РУ'], 'sort' => 1]);

    expect($this->getJson('/api/v1/forum')->json('data.categories.0.title'))->toBe('ТҶ')
        ->and($this->getJson('/api/v1/ru/forum')->json('data.categories.0.title'))->toBe('РУ');
});

test('the forum seeder reproduces the frontend mock', function () {
    $this->seed(ForumSeeder::class);
    actingAsForumApi();

    $data = $this->getJson('/api/v1/forum')->assertOk()->json('data');

    expect($data['categories'])->toHaveCount(4)
        ->and($data['topics'])->toHaveCount(6)
        ->and($data['stats'])->toBe(['members' => '8 420', 'topics' => '470', 'posts' => '3 512', 'online' => '63']);

    $general = collect($data['categories'])->firstWhere('id', 'general');
    expect($general['title'])->toBe('Умумӣ')
        ->and($general['topics'])->toBe(124)
        ->and($general['posts'])->toBe(980)
        ->and($general['icon'])->toBe('MessagesSquare');

    expect($data['topics'][0]['id'])->toBe('t1')
        ->and($data['topics'][0]['pinned'])->toBeTrue()
        ->and($data['topics'][0]['author'])->toBe('Дилшод_77')
        ->and($data['topics'][0]['category'])->toBe('alerts')
        ->and($data['topics'][0]['lastActivity'])->toBe('2 соат пеш');
});

test('the forum seeder is idempotent', function () {
    $this->seed(ForumSeeder::class);
    $this->seed(ForumSeeder::class);

    expect(ForumCategory::query()->count())->toBe(4)
        ->and(ForumTopic::query()->count())->toBe(6)
        ->and(ForumStat::query()->count())->toBe(1);
});

test('creating an active forum record revalidates, an inactive one does not', function () {
    Queue::fake();
    ForumCategory::factory()->create();
    ForumTopic::factory()->create();
    Queue::assertPushed(
        SendRevalidationRequest::class,
        fn (SendRevalidationRequest $job): bool => in_array('forum', $job->tags, true),
    );

    Queue::fake();
    ForumCategory::factory()->inactive()->create();
    ForumTopic::factory()->inactive()->create();
    Queue::assertNotPushed(SendRevalidationRequest::class);
});

test('saving the forum stats singleton revalidates forum', function () {
    Queue::fake();

    ForumStat::query()->create(['members' => '100', 'topics' => '10', 'posts' => '50', 'online' => '5']);

    Queue::assertPushed(
        SendRevalidationRequest::class,
        fn (SendRevalidationRequest $job): bool => in_array('forum', $job->tags, true),
    );
});
