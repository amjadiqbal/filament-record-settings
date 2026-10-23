<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\LegacyPage;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\Team;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\TeamPolicy;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\TeamSettings;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\UserPreferences;
use Filament\Notifications\Notification;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = $this->makeUser('Admin');
    $this->actingAs($this->user);
});

it('fills the signed-in user\'s settings over the model defaults', function (): void {
    $this->user->update(['settings' => ['theme' => 'dark']]);

    Livewire::test(UserPreferences::class)
        ->assertOk()
        ->assertSee('Preferences')
        ->assertSchemaStateSet(['theme' => 'dark', 'email_digest' => true]);
});

it('saves into the settings column and keeps keys the form does not show', function (): void {
    $this->user->update(['settings' => ['theme' => 'light', 'sidebar' => 'collapsed']]);

    Livewire::test(UserPreferences::class)
        ->fillForm(['theme' => 'dark', 'email_digest' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->user->fresh()->settings)->toBe(['theme' => 'dark', 'sidebar' => 'collapsed', 'email_digest' => false]);
    Notification::assertNotified('Settings saved');
});

it('validates the form before saving', function (): void {
    Livewire::test(UserPreferences::class)
        ->fillForm(['theme' => null])
        ->call('save')
        ->assertHasFormErrors(['theme' => 'required']);

    expect($this->user->fresh()->settings)->toBeNull();
});

it('edits another model through glorand, with glorand defaults', function (): void {
    $team = Team::query()->create(['name' => 'Core']);

    Livewire::test(TeamSettings::class)
        ->assertSchemaStateSet(['locale' => 'en', 'seats' => 5])
        ->fillForm(['locale' => 'de', 'seats' => 12])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($team->fresh()->settings()->get('locale'))->toBe('de')
        ->and((int) $team->fresh()->settings()->get('seats'))->toBe(12);
});

it('shows a glorand settingsRules failure on the field', function (): void {
    $team = Team::query()->create(['name' => 'Core']);

    Livewire::test(TeamSettings::class)
        ->fillForm(['seats' => 80])
        ->call('save')
        ->assertHasErrors(['data.seats']);

    expect((int) $team->fresh()->settings()->get('seats'))->toBe(5);
});

it('denies the page when the record policy says no', function (): void {
    Team::query()->create(['name' => 'Core']);
    TeamPolicy::$canUpdateSettings = false;

    expect(TeamSettings::canAccess())->toBeFalse();
    Livewire::test(TeamSettings::class)->assertForbidden();
});

it('denies the page when there is no record', function (): void {
    expect(TeamSettings::canAccess())->toBeFalse();

    auth()->logout();
    expect(UserPreferences::canAccess())->toBeFalse();
});

it('serves the page in the panel', function (): void {
    $this->get(UserPreferences::getUrl())->assertOk()->assertSee('Preferences');
});

it('keeps a page migrated from quadrubo working through getSettingRecord()', function (): void {
    $team = Team::query()->create(['name' => 'Core']);

    expect(LegacyPage::getSettingsRecord()?->is($team))->toBeTrue();

    Livewire::test(LegacyPage::class)
        ->fillForm(['locale' => 'de'])
        ->call('save');

    expect($team->fresh()->settings()->get('locale'))->toBe('de');
});
