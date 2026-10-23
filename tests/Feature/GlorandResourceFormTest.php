<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRecordSettings\Actions\EditRecordSettingsAction;
use AmjadIqbal\FilamentRecordSettings\Stores\GlorandStore;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\EditOrg;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\EditTeam;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\Org;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\Team;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->actingAs($this->makeUser('Admin'));
});

it('edits a glorand field-driver setting in a resource form and keeps the other settings', function (): void {
    $team = Team::query()->findOrFail(Team::query()->create(['name' => 'Core'])->id);
    $team->settings()->setMultiple(['locale' => 'en', 'seats' => 9, 'beta' => true]);

    Livewire::test(EditTeam::class, ['record' => $team->getRouteKey()])
        ->assertSchemaStateSet(['settings.locale' => 'en'])
        ->fillForm(['name' => 'Core team', 'settings.locale' => 'de'])
        ->call('save')
        ->assertHasNoFormErrors();

    $team = Team::query()->findOrFail($team->id);

    expect($team->name)->toBe('Core team')
        ->and($team->settings()->get('locale'))->toBe('de')
        ->and((int) $team->settings()->get('seats'))->toBe(9)
        ->and($team->settings()->get('beta'))->toBeTrue();
});

it('shows a glorand settingsRules failure under the resource field and saves nothing', function (): void {
    $team = Team::query()->findOrFail(Team::query()->create(['name' => 'Core'])->id);

    Livewire::test(EditTeam::class, ['record' => $team->getRouteKey()])
        ->fillForm(['name' => 'Renamed', 'settings.seats' => 80])
        ->call('save')
        ->assertHasErrors(['data.settings.seats']);

    $team = Team::query()->findOrFail($team->id);

    expect($team->name)->toBe('Core')
        ->and((int) $team->settings()->get('seats'))->toBe(5);
});

it('edits a glorand table-driver setting in a resource form without touching the model table', function (): void {
    $org = Org::query()->create(['name' => 'Acme']);
    $org->settings()->setMultiple(['locale' => 'en', 'plan' => 'pro']);

    Livewire::test(EditOrg::class, ['record' => $org->getRouteKey()])
        ->assertSchemaStateSet(['settings.locale' => 'en'])
        ->fillForm(['name' => 'Acme Ltd', 'settings.locale' => 'de'])
        ->call('save')
        ->assertHasNoFormErrors();

    $org = Org::query()->findOrFail($org->id);

    expect($org->name)->toBe('Acme Ltd')
        ->and($org->settings()->all())->toMatchArray(['locale' => 'de', 'plan' => 'pro']);
});

it('saves a glorand table-driver record from the modal action', function (): void {
    $org = Org::query()->create(['name' => 'Acme']);

    $action = EditRecordSettingsAction::make()->record($org);

    expect($action->getSettingsColumn())->toBeNull()
        ->and((fn () => $this->getSettingsStoreFor($org))->call($action))
        ->toBeInstanceOf(GlorandStore::class);
});
