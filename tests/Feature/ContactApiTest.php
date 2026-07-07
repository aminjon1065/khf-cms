<?php

use App\Jobs\SendRevalidationRequest;
use App\Models\ContactOffice;
use App\Models\Hotline;
use App\Models\User;
use Database\Seeders\ContactSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function actingAsContactApi(): void
{
    Sanctum::actingAs(User::factory()->create());
}

test('contacts endpoint requires a bearer token', function () {
    Hotline::factory()->create();

    $this->getJson('/api/v1/contacts')->assertUnauthorized();
});

test('returns the contract shape with hotlines, headOffice and offices', function () {
    actingAsContactApi();
    Hotline::factory()->create();
    ContactOffice::factory()->head()->create();
    ContactOffice::factory()->create(['sort' => 1]);

    $this->getJson('/api/v1/contacts')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'hotlines' => [['number', 'label', 'note', 'primary']],
                'headOffice' => ['region', 'address', 'phone', 'email', 'hours'],
                'offices' => [['region', 'address', 'phone', 'email', 'hours']],
            ],
        ]);
});

test('the primary flag reflects the stored value', function () {
    actingAsContactApi();
    Hotline::factory()->primary()->create(['sort' => 1]);
    Hotline::factory()->create(['sort' => 2]);

    $hotlines = $this->getJson('/api/v1/contacts')->assertOk()->json('data.hotlines');

    expect($hotlines[0]['primary'])->toBeTrue()
        ->and($hotlines[1]['primary'])->toBeFalse();
});

test('office phone and email are always strings', function () {
    actingAsContactApi();
    ContactOffice::factory()->head()->create();

    $head = $this->getJson('/api/v1/contacts')->assertOk()->json('data.headOffice');

    expect($head['phone'])->toBeString()
        ->and($head['email'])->toBeString();
});

test('the head office is served separately and excluded from the offices list', function () {
    actingAsContactApi();
    ContactOffice::factory()->head()->create(['region' => ['tj' => 'Марказ'], 'sort' => 1]);
    ContactOffice::factory()->create(['region' => ['tj' => 'Хатлон'], 'sort' => 2]);
    ContactOffice::factory()->create(['region' => ['tj' => 'Суғд'], 'sort' => 3]);

    $data = $this->getJson('/api/v1/contacts')->assertOk()->json('data');

    expect($data['headOffice']['region'])->toBe('Марказ')
        ->and($data['offices'])->toHaveCount(2)
        ->and(collect($data['offices'])->pluck('region')->all())->toBe(['Хатлон', 'Суғд']);
});

test('headOffice is null when no office is flagged as head', function () {
    actingAsContactApi();
    ContactOffice::factory()->create();

    expect($this->getJson('/api/v1/contacts')->assertOk()->json('data.headOffice'))->toBeNull();
});

test('only active entries are returned, in manual order', function () {
    actingAsContactApi();
    Hotline::factory()->create(['number' => 'B', 'sort' => 2]);
    Hotline::factory()->create(['number' => 'A', 'sort' => 1]);
    Hotline::factory()->inactive()->create(['number' => 'X', 'sort' => 0]);
    ContactOffice::factory()->inactive()->create(['sort' => 0]);

    $data = $this->getJson('/api/v1/contacts')->assertOk()->json('data');

    expect($data['hotlines'])->toHaveCount(2)
        ->and($data['hotlines'][0]['number'])->toBe('A')
        ->and($data['hotlines'][1]['number'])->toBe('B')
        ->and($data['offices'])->toHaveCount(0);
});

test('translatable fields resolve to the request locale', function () {
    actingAsContactApi();
    Hotline::factory()->create(['label' => ['tj' => 'ТҶ', 'ru' => 'РУ'], 'sort' => 1]);

    expect($this->getJson('/api/v1/contacts')->json('data.hotlines.0.label'))->toBe('ТҶ')
        ->and($this->getJson('/api/v1/ru/contacts')->json('data.hotlines.0.label'))->toBe('РУ');
});

test('the contact seeder reproduces the frontend mock', function () {
    $this->seed(ContactSeeder::class);
    actingAsContactApi();

    $data = $this->getJson('/api/v1/contacts')->assertOk()->json('data');

    expect($data['hotlines'])->toHaveCount(4)
        ->and($data['offices'])->toHaveCount(4)
        ->and($data['hotlines'][0]['number'])->toBe('112')
        ->and($data['hotlines'][0]['label'])->toBe('Хадамоти ягонаи наҷот')
        ->and($data['hotlines'][0]['primary'])->toBeTrue()
        ->and($data['hotlines'][1]['primary'])->toBeFalse()
        ->and($data['headOffice']['region'])->toBe('Дастгоҳи марказӣ')
        ->and($data['headOffice']['address'])->toBe('734013, ш. Душанбе, кӯчаи Лоҳутӣ 26')
        ->and($data['headOffice']['email'])->toBe('info@khf.tj')
        ->and($data['offices'][0]['region'])->toBe('вилояти Хатлон')
        ->and($data['offices'][0]['phone'])->toBe('(992 3222) 2-22-12');
});

test('the contact seeder is idempotent', function () {
    $this->seed(ContactSeeder::class);
    $this->seed(ContactSeeder::class);

    expect(Hotline::query()->count())->toBe(4)
        ->and(ContactOffice::query()->count())->toBe(5)
        ->and(ContactOffice::query()->where('is_head', true)->count())->toBe(1);
});

test('marking an office as head demotes the previous head', function () {
    $first = ContactOffice::factory()->head()->create();
    $second = ContactOffice::factory()->head()->create();

    expect($first->fresh()->is_head)->toBeFalse()
        ->and($second->fresh()->is_head)->toBeTrue()
        ->and(ContactOffice::query()->where('is_head', true)->count())->toBe(1);
});

test('creating a hotline without an explicit active flag still revalidates', function () {
    Queue::fake();

    Hotline::create([
        'number' => '112',
        'label' => ['tj' => 'наҷот'],
        'note' => ['tj' => 'нота'],
        'sort' => 1,
    ]);

    Queue::assertPushed(
        SendRevalidationRequest::class,
        fn (SendRevalidationRequest $job): bool => in_array('contacts', $job->tags, true),
    );
});

test('demoting the live active head via an inactive head still revalidates contacts', function () {
    $liveHead = ContactOffice::factory()->head()->create();

    Queue::fake();

    // An inactive head silently strips the live head; its own gate stays closed,
    // so the demotion itself must flush the contacts cache.
    ContactOffice::factory()->head()->inactive()->create();

    expect($liveHead->fresh()->is_head)->toBeFalse();

    Queue::assertPushed(
        SendRevalidationRequest::class,
        fn (SendRevalidationRequest $job): bool => in_array('contacts', $job->tags, true),
    );
});

test('creating an active contact revalidates, an inactive one does not', function () {
    Queue::fake();
    ContactOffice::factory()->create();
    Queue::assertPushed(
        SendRevalidationRequest::class,
        fn (SendRevalidationRequest $job): bool => in_array('contacts', $job->tags, true),
    );

    Queue::fake();
    Hotline::factory()->inactive()->create();
    ContactOffice::factory()->inactive()->create();
    Queue::assertNotPushed(SendRevalidationRequest::class);
});
