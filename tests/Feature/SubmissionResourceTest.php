<?php

use App\Enums\SubmissionStatus;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\Reports\Pages\ListReports;
use App\Filament\Resources\Reports\ReportResource;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Models\ContactMessage;
use App\Models\Report;
use App\Models\Subscription;
use App\Policies\ReportPolicy;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('staff can access the three submission inboxes', function () {
    Report::factory()->create();
    ContactMessage::factory()->create();
    Subscription::factory()->create();

    foreach (['reports', 'contact-messages', 'subscriptions'] as $slug) {
        $this->actingAs(createAdminUser())->get("/admin/{$slug}")->assertSuccessful();
        $this->actingAs(createEditorUser())->get("/admin/{$slug}")->assertSuccessful();
    }
});

test('submissions cannot be created from the panel', function () {
    $this->actingAs(createAdminUser());

    expect(ReportResource::canCreate())->toBeFalse()
        ->and(ContactMessageResource::canCreate())->toBeFalse()
        ->and(SubscriptionResource::canCreate())->toBeFalse();
});

test('the status filter narrows the inbox', function () {
    $new = Report::factory()->create();
    $closed = Report::factory()->status(SubmissionStatus::Closed)->create();

    Livewire::actingAs(createEditorUser())
        ->test(ListReports::class)
        ->assertCanSeeTableRecords([$new, $closed])
        ->filterTable('status', SubmissionStatus::Closed->value)
        ->assertCanSeeTableRecords([$closed])
        ->assertCanNotSeeTableRecords([$new]);
});

test('editors triage and update status, but only admins delete', function () {
    $report = Report::factory()->create();
    $admin = createAdminUser();
    $editor = createEditorUser();
    $policy = new ReportPolicy;

    expect($policy->viewAny($editor))->toBeTrue()
        ->and($policy->update($editor, $report))->toBeTrue()
        ->and($policy->create($editor))->toBeFalse()
        ->and($policy->delete($editor, $report))->toBeFalse()
        ->and($policy->delete($admin, $report))->toBeTrue()
        // Filament bulk delete authorizes via deleteAny(), not delete().
        ->and($policy->deleteAny($editor))->toBeFalse()
        ->and($policy->deleteAny($admin))->toBeTrue();
});

test('the delete bulk action is hidden from editors and available to admins', function (string $page, callable $factory) {
    $factory();

    Livewire::actingAs(createEditorUser())
        ->test($page)
        ->assertTableBulkActionHidden('delete');

    Livewire::actingAs(createAdminUser())
        ->test($page)
        ->assertTableBulkActionVisible('delete');
})->with([
    'reports' => [ListReports::class, fn () => Report::factory()->create()],
    'contact messages' => [ListContactMessages::class, fn () => ContactMessage::factory()->create()],
    'subscriptions' => [ListSubscriptions::class, fn () => Subscription::factory()->create()],
]);

test('an admin can bulk-delete but an editor cannot mutate the record', function () {
    $report = Report::factory()->create();

    // Editor: action unavailable, record survives.
    Livewire::actingAs(createEditorUser())
        ->test(ListReports::class)
        ->assertTableBulkActionHidden('delete');
    expect(Report::query()->whereKey($report->getKey())->exists())->toBeTrue();

    // Admin: bulk delete removes it.
    Livewire::actingAs(createAdminUser())
        ->test(ListReports::class)
        ->callTableBulkAction('delete', [$report]);
    expect(Report::query()->whereKey($report->getKey())->exists())->toBeFalse();
});

test('the csv export bulk action returns a download', function () {
    $report = Report::factory()->create();

    Livewire::actingAs(createEditorUser())
        ->test(ListReports::class)
        ->callTableBulkAction('exportCsv', [$report])
        ->assertFileDownloaded('reports.csv');
});
