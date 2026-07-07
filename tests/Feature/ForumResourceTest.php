<?php

use App\Filament\Pages\ManageForumStats;
use App\Filament\Resources\ForumCategories\Pages\CreateForumCategory;
use App\Filament\Resources\ForumCategories\Pages\EditForumCategory;
use App\Filament\Resources\ForumTopics\Pages\CreateForumTopic;
use App\Models\ForumCategory;
use App\Models\ForumStat;
use App\Models\ForumTopic;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('staff can access the forum categories and topics resources', function () {
    ForumCategory::factory()->create();
    ForumTopic::factory()->create();

    $this->actingAs(createAdminUser())->get('/admin/forum-categories')->assertSuccessful();
    $this->actingAs(createEditorUser())->get('/admin/forum-categories')->assertSuccessful();
    $this->actingAs(createAdminUser())->get('/admin/forum-topics')->assertSuccessful();
    $this->actingAs(createEditorUser())->get('/admin/forum-topics')->assertSuccessful();
});

test('editor can create a forum category', function () {
    Livewire::actingAs(createEditorUser())
        ->test(CreateForumCategory::class)
        ->fillForm([
            'slug' => 'general',
            'title.tj' => 'Умумӣ',
            'description.tj' => 'Общие вопросы',
            'topics' => 124,
            'posts' => 980,
            'icon' => 'MessagesSquare',
            'sort' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $category = ForumCategory::query()->firstOrFail();

    expect($category->slug)->toBe('general')
        ->and($category->icon)->toBe('MessagesSquare')
        ->and($category->topics)->toBe(124)
        ->and($category->getTranslation('title', 'tj'))->toBe('Умумӣ');
});

test('a duplicate forum category slug is rejected', function () {
    ForumCategory::factory()->create(['slug' => 'general']);

    Livewire::actingAs(createEditorUser())
        ->test(CreateForumCategory::class)
        ->fillForm([
            'slug' => 'general',
            'title.tj' => 'Дигар',
            'description.tj' => 'Тавсиф',
            'topics' => 0,
            'posts' => 0,
            'icon' => 'HelpCircle',
            'sort' => 2,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

test('editor can create a forum topic bound to a category', function () {
    ForumCategory::factory()->create(['slug' => 'alerts']);

    Livewire::actingAs(createEditorUser())
        ->test(CreateForumTopic::class)
        ->fillForm([
            'slug' => 't1',
            'title.tj' => 'Правила поведения при сходе селя',
            'category' => 'alerts',
            'author' => 'Дилшод_77',
            'replies' => 42,
            'views' => 1820,
            'last_activity.tj' => '2 соат пеш',
            'pinned' => true,
            'sort' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $topic = ForumTopic::query()->firstOrFail();

    expect($topic->slug)->toBe('t1')
        ->and($topic->category)->toBe('alerts')
        ->and($topic->pinned)->toBeTrue()
        ->and($topic->getTranslation('title', 'tj'))->toBe('Правила поведения при сходе селя');
});

test('a duplicate forum topic slug is rejected', function () {
    ForumCategory::factory()->create(['slug' => 'alerts']);
    ForumTopic::factory()->create(['slug' => 't1']);

    Livewire::actingAs(createEditorUser())
        ->test(CreateForumTopic::class)
        ->fillForm([
            'slug' => 't1',
            'title.tj' => 'Дигар мавзӯъ',
            'category' => 'alerts',
            'author' => 'Гулнора',
            'replies' => 0,
            'views' => 0,
            'last_activity.tj' => 'имрӯз',
            'pinned' => false,
            'sort' => 2,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

test('editing a forum category preserves the other locale translations', function () {
    $category = ForumCategory::factory()->create([
        'title' => ['tj' => 'ТҶ', 'ru' => 'РУ', 'en' => 'EN'],
    ]);

    Livewire::actingAs(createAdminUser())
        ->test(EditForumCategory::class, ['record' => $category->getRouteKey()])
        ->assertFormSet([
            'title.ru' => 'РУ',
            'title.en' => 'EN',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($category->fresh()->getTranslation('title', 'ru'))->toBe('РУ');
});

test('only admin can access the forum stats page', function () {
    $this->actingAs(createAdminUser())->get('/admin/forum-stats')->assertSuccessful();
    $this->actingAs(createEditorUser())->get('/admin/forum-stats')->assertForbidden();
});

test('saving the forum stats page persists the singleton', function () {
    Livewire::actingAs(createAdminUser())
        ->test(ManageForumStats::class)
        ->fillForm([
            'members' => '8 420',
            'topics' => '470',
            'posts' => '3 512',
            'online' => '63',
        ])
        ->call('save');

    $stats = ForumStat::current();

    expect($stats->members)->toBe('8 420')
        ->and($stats->posts)->toBe('3 512')
        ->and($stats->online)->toBe('63');
});
