<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Pages;

use AmjadIqbal\FilamentRecordSettings\Concerns\ResolvesSettingsStore;
use AmjadIqbal\FilamentRecordSettings\Contracts\SettingsStore;
use AmjadIqbal\FilamentRecordSettings\RecordSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * A page that edits the settings of one record: by default the signed-in user's, so a subclass with only a
 * `form()` is a per-user preferences page. Override `getSettingsRecord()` for a tenant, a team or any other model.
 *
 * @property-read Schema $form
 */
abstract class RecordSettingsPage extends Page
{
    use CanUseDatabaseTransactions;
    use ResolvesSettingsStore;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    /**
     * The JSON column that holds the settings. Leave null to use glorand/laravel-model-settings when the model has
     * it, or the `settings` column otherwise.
     */
    protected static ?string $settingsColumn = null;

    /**
     * The policy ability checked on the record, when its policy defines it. Null skips the check.
     */
    protected static ?string $settingsAbility = 'updateSettings';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    /**
     * The record whose settings this page edits. Null (nobody signed in, no tenant) denies access.
     */
    public static function getSettingsRecord(): ?Model
    {
        // The name quadrubo/filament-model-settings pages use, so a migrated page keeps working.
        $legacy = 'getSettingRecord';

        if (method_exists(static::class, $legacy)) {
            $record = static::$legacy();

            return $record instanceof Model ? $record : null;
        }

        $user = Filament::auth()->user();

        return $user instanceof Model ? $user : null;
    }

    public static function canAccess(): bool
    {
        if (! parent::canAccess()) {
            return false;
        }

        $record = static::getSettingsRecord();

        return $record !== null && RecordSettings::can($record, static::$settingsAbility);
    }

    /**
     * Defaults for the column store. A glorand model keeps its own `$defaultSettings`.
     *
     * @return array<string, mixed>
     */
    public static function getSettingsDefaults(): array
    {
        return [];
    }

    public static function getSettingsStore(): SettingsStore
    {
        /** @var Model $record */
        $record = static::getSettingsRecord();

        return static::resolveSettingsStore($record, static::$settingsColumn, static::getSettingsDefaults());
    }

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $data = $this->mutateFormDataBeforeFill(static::getSettingsStore()->get($this->getRecord()));

        $this->form->fill($data);

        $this->callHook('afterFill');
    }

    public function getRecord(): Model
    {
        $record = static::getSettingsRecord();

        abort_if($record === null, 403);

        return $record;
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeSave($data);

            $this->callHook('beforeSave');

            static::writeSettings(static::getSettingsStore(), $this->getRecord(), $data, 'data');

            $this->callHook('afterSave');
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->commitDatabaseTransaction();

        $this->getSavedNotification()?->send();

        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode($redirectUrl));
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    protected function getSavedNotification(): ?Notification
    {
        $title = $this->getSavedNotificationTitle();

        if (blank($title)) {
            return null;
        }

        return Notification::make()
            ->success()
            ->title($title);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('filament-record-settings::record-settings.notifications.saved.title');
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->model($this->getRecord())
            ->operation('edit')
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->sticky($this->areFormActionsSticky())
                    ->key('form-actions'),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label(__('filament-record-settings::record-settings.actions.save.label'))
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }
}
