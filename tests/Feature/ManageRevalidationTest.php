<?php

use App\Filament\Pages\ManageRevalidation;
use App\Jobs\SendRevalidationRequest;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('admin can access the revalidation page', function () {
    $this->actingAs(createAdminUser())->get('/admin/revalidation')->assertSuccessful();
});

test('editor cannot access the revalidation page', function () {
    $this->actingAs(createEditorUser())->get('/admin/revalidation')->assertForbidden();
});

test('the flush action queues an all-tags revalidation', function () {
    Queue::fake();

    Livewire::actingAs(createAdminUser())
        ->test(ManageRevalidation::class)
        ->callAction('flush')
        ->assertNotified('Сброс кеша поставлен в очередь');

    Queue::assertPushed(SendRevalidationRequest::class, fn (SendRevalidationRequest $job) => in_array('forum', $job->tags, true)
        && in_array('news', $job->tags, true));
});

test('the ping action reports a failure when unconfigured', function () {
    Livewire::actingAs(createAdminUser())
        ->test(ManageRevalidation::class)
        ->callAction('ping')
        ->assertNotified('Ошибка соединения');
});

test('the ping action succeeds against a fake frontend', function () {
    config()->set('khf.revalidate.frontend_url', 'https://front.test');
    config()->set('khf.revalidate.secret', 's3cret');
    Http::fake(['*' => Http::response(['ok' => true])]);

    Livewire::actingAs(createAdminUser())
        ->test(ManageRevalidation::class)
        ->callAction('ping')
        ->assertNotified('Соединение успешно');

    Http::assertSent(fn ($request) => $request->url() === 'https://front.test/api/revalidate'
        && $request->header('x-revalidate-secret')[0] === 's3cret');
});
