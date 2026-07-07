<?php

use App\Enums\ProgramStatus;
use App\Jobs\SendRevalidationRequest;
use App\Models\Direction;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\ActivitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function actingAsActivityApi(): void
{
    Sanctum::actingAs(User::factory()->create());
}

test('activities endpoint requires a bearer token', function () {
    Direction::factory()->create();

    $this->getJson('/api/v1/activities')->assertUnauthorized();
});

test('returns the contract shape with directions and programs', function () {
    actingAsActivityApi();
    Direction::factory()->create();
    Program::factory()->create();

    $this->getJson('/api/v1/activities')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'directions' => [['id', 'icon', 'title', 'description', 'stat' => ['value', 'label']]],
                'programs' => [['title', 'period', 'status', 'description']],
            ],
        ]);
});

test('direction id is the slug and only active directions are returned in order', function () {
    actingAsActivityApi();
    Direction::factory()->create(['slug' => 'second', 'sort' => 2]);
    Direction::factory()->create(['slug' => 'first', 'sort' => 1]);
    Direction::factory()->inactive()->create(['slug' => 'hidden', 'sort' => 0]);

    $directions = $this->getJson('/api/v1/activities')->assertOk()->json('data.directions');

    expect($directions)->toHaveCount(2)
        ->and($directions[0]['id'])->toBe('first')
        ->and($directions[1]['id'])->toBe('second');
});

test('programme status is the localized label', function () {
    actingAsActivityApi();
    Program::factory()->status(ProgramStatus::Active)->create(['sort' => 1]);

    expect($this->getJson('/api/v1/activities')->json('data.programs.0.status'))->toBe('Амалкунанда')
        ->and($this->getJson('/api/v1/ru/activities')->json('data.programs.0.status'))->toBe('Действует');
});

test('translatable direction fields resolve to the request locale', function () {
    actingAsActivityApi();
    Direction::factory()->create(['title' => ['tj' => 'ТҶ', 'ru' => 'РУ'], 'sort' => 1]);

    expect($this->getJson('/api/v1/activities')->json('data.directions.0.title'))->toBe('ТҶ')
        ->and($this->getJson('/api/v1/ru/activities')->json('data.directions.0.title'))->toBe('РУ');
});

test('the activity seeder reproduces the frontend mock', function () {
    $this->seed(ActivitySeeder::class);
    actingAsActivityApi();

    $data = $this->getJson('/api/v1/activities')->assertOk()->json('data');

    expect($data['directions'])->toHaveCount(6)
        ->and($data['programs'])->toHaveCount(4);

    $rescue = collect($data['directions'])->firstWhere('id', 'rescue');

    expect($rescue['icon'])->toBe('LifeBuoy')
        ->and($rescue['title'])->toBe('Корҳои ҷустуҷӯию наҷотдиҳӣ')
        ->and($rescue['stat']['value'])->toBe('12 480')
        ->and($rescue['stat']['label'])->toBe('спасено за год')
        ->and($data['programs'][0]['period'])->toBe('2016–2030')
        ->and($data['programs'][0]['status'])->toBe('Амалкунанда')
        ->and($data['programs'][3]['status'])->toBe('Ба нақша');
});

test('the activity seeder is idempotent', function () {
    $this->seed(ActivitySeeder::class);
    $this->seed(ActivitySeeder::class);

    expect(Direction::query()->count())->toBe(6)
        ->and(Program::query()->count())->toBe(4);
});

test('creating an active direction revalidates the activities tag', function () {
    Queue::fake();

    Direction::factory()->create();

    Queue::assertPushed(
        SendRevalidationRequest::class,
        fn (SendRevalidationRequest $job): bool => in_array('activities', $job->tags, true),
    );
});

test('creating an inactive direction or programme does not revalidate', function () {
    Queue::fake();

    Direction::factory()->inactive()->create();
    Program::factory()->inactive()->create();

    Queue::assertNotPushed(SendRevalidationRequest::class);
});
