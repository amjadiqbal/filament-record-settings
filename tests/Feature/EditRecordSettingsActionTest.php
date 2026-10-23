<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRecordSettings\Actions\EditRecordSettingsAction;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\CreateProject;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\EditProject;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\ListProjects;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\Project;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\ProjectPolicy;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Field;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->actingAs($this->makeUser('Admin'));
    $this->project = Project::query()->create(['name' => 'Apollo', 'prefs' => ['public' => true, 'pinned' => 3]]);
});

it('opens the row action filled with the record settings and defaults', function (): void {
    Livewire::test(ListProjects::class)
        ->mountAction(TestAction::make('editSettings')->table($this->project))
        ->assertSchemaStateSet(['color' => 'teal', 'archived_view' => null]);
});

it('saves the row action into the given column and keeps the other keys', function (): void {
    Livewire::test(ListProjects::class)
        ->callAction(TestAction::make('editSettings')->table($this->project), data: ['color' => 'sky', 'archived_view' => true])
        ->assertHasNoFormErrors()
        ->assertNotified('Settings saved');

    expect($this->project->fresh()->prefs)->toBe(['public' => true, 'pinned' => 3, 'color' => 'sky', 'archived_view' => true]);
});

it('hides the row action when the policy denies updateSettings', function (): void {
    ProjectPolicy::$canUpdateSettings = false;

    Livewire::test(ListProjects::class)
        ->assertActionHidden(TestAction::make('editSettings')->table($this->project));
});

it('keeps the policy check when the action has its own authorize()', function (): void {
    ProjectPolicy::$canUpdateSettings = false;

    $action = EditRecordSettingsAction::make()->record($this->project)->authorize(fn (): bool => true);

    expect($action->isAuthorized())->toBeFalse();

    ProjectPolicy::$canUpdateSettings = true;

    expect(EditRecordSettingsAction::make()->record($this->project)->authorize(fn (): bool => false)->isAuthorized())->toBeFalse()
        ->and(EditRecordSettingsAction::make()->record($this->project)->authorize(fn (): bool => true)->isAuthorized())->toBeTrue();
});

it('fills recordSetting() fields on the edit page and saves them without losing other keys', function (): void {
    Livewire::test(EditProject::class, ['record' => $this->project->getRouteKey()])
        ->assertSchemaStateSet(['name' => 'Apollo', 'prefs.public' => true])
        ->fillForm(['name' => 'Artemis', 'prefs.public' => false, 'prefs.visibility' => 'org'])
        ->call('save')
        ->assertHasNoFormErrors();

    $project = $this->project->fresh();

    expect($project->name)->toBe('Artemis')
        ->and($project->prefs)->toBe(['public' => false, 'pinned' => 3, 'visibility' => 'org']);
});

it('saves recordSetting() fields on create', function (): void {
    Livewire::test(CreateProject::class)
        ->fillForm(['name' => 'Gemini', 'prefs.public' => true, 'prefs.visibility' => 'team'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Project::query()->where('name', 'Gemini')->sole()->prefs)->toBe(['public' => true, 'visibility' => 'team']);
});

it('registers the isModelSetting() alias for migrated forms', function (): void {
    expect(Field::hasMacro('recordSetting'))->toBeTrue()
        ->and(Field::hasMacro('isModelSetting'))->toBeTrue();
});
