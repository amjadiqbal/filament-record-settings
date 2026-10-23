<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Actions;

use AmjadIqbal\FilamentRecordSettings\Concerns\ResolvesSettingsStore;
use AmjadIqbal\FilamentRecordSettings\Contracts\SettingsStore;
use AmjadIqbal\FilamentRecordSettings\RecordSettings;
use Closure;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

/**
 * A modal that edits one record's settings: a table row action, or a header action on a record page.
 *
 *     EditRecordSettingsAction::make()->schema([Toggle::make('email_digest'), ...])
 */
class EditRecordSettingsAction extends Action
{
    use ResolvesSettingsStore;

    protected string|Closure|null $settingsColumn = null;

    /**
     * @var array<string, mixed> | Closure
     */
    protected array|Closure $settingsDefaults = [];

    protected string|Closure|null $settingsAbility = 'updateSettings';

    public static function getDefaultName(): ?string
    {
        return 'editSettings';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-record-settings::record-settings.actions.edit.label'));

        $this->modalHeading(fn (?Model $record): string => __('filament-record-settings::record-settings.actions.edit.modal_heading'));

        $this->modalSubmitActionLabel(__('filament-record-settings::record-settings.actions.save.label'));

        $this->successNotificationTitle(__('filament-record-settings::record-settings.notifications.saved.title'));

        $this->icon(Heroicon::OutlinedCog6Tooth);

        $this->color('gray');

        $this->authorize(fn (?Model $record): bool => $record !== null && RecordSettings::can($record, $this->getSettingsAbility()));

        $this->fillForm(fn (Model $record): array => $this->getSettingsStoreFor($record)->get($record));

        $this->action(function (Model $record, array $data): void {
            static::writeSettings($this->getSettingsStoreFor($record), $record, $data, 'mountedActions.'.($this->getNestingIndex() ?? 0).'.data');

            $this->success();
        });
    }

    /**
     * The JSON column that holds the settings. Without one, glorand/laravel-model-settings is used when the model
     * has it, or the `settings` column otherwise.
     */
    public function settingsColumn(string|Closure|null $column): static
    {
        $this->settingsColumn = $column;

        return $this;
    }

    /**
     * Defaults for the column store. A glorand model keeps its own `$defaultSettings`.
     *
     * @param  array<string, mixed> | Closure  $defaults
     */
    public function settingsDefaults(array|Closure $defaults): static
    {
        $this->settingsDefaults = $defaults;

        return $this;
    }

    /**
     * The policy ability checked on the record, when its policy defines it. Null skips the check.
     */
    public function settingsAbility(string|Closure|null $ability): static
    {
        $this->settingsAbility = $ability;

        return $this;
    }

    public function getSettingsColumn(): ?string
    {
        return $this->evaluate($this->settingsColumn);
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettingsDefaults(): array
    {
        return (array) $this->evaluate($this->settingsDefaults);
    }

    public function getSettingsAbility(): ?string
    {
        return $this->evaluate($this->settingsAbility);
    }

    protected function getSettingsStoreFor(Model $record): SettingsStore
    {
        return static::resolveSettingsStore($record, $this->getSettingsColumn(), $this->getSettingsDefaults());
    }
}
