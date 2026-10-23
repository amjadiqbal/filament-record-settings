<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/amjadiqbal/filament-record-settings/main/art/banner-dark.png">
    <img alt="Record Settings: per-user and per-tenant settings for Filament" src="https://raw.githubusercontent.com/amjadiqbal/filament-record-settings/main/art/banner-light.png" width="100%">
  </picture>
</p>

# Record Settings for Filament

Edit per-record settings in Filament 4 and 5: a user's preferences, a team's or a tenant's options, any model's
settings. Use a settings page, a modal action on a table row or record page, or fields inside a resource form. The
settings live in [glorand/laravel-model-settings](https://github.com/glorand/laravel-model-settings) (v8 or v9, any
driver) or in a plain JSON column.

**Docs:** [amjad.com.pk/filament/plugins/filament-record-settings](https://amjad.com.pk/filament/plugins/filament-record-settings)

[![Latest Version](https://img.shields.io/packagist/v/amjadiqbal/filament-record-settings.svg?style=flat-square)](https://packagist.org/packages/amjadiqbal/filament-record-settings)
[![PHP Version](https://img.shields.io/packagist/php-v/amjadiqbal/filament-record-settings?style=flat-square)](https://packagist.org/packages/amjadiqbal/filament-record-settings)
[![License](https://img.shields.io/packagist/l/amjadiqbal/filament-record-settings.svg?style=flat-square)](LICENSE.md)

![A team settings page built with Record Settings in a Filament panel](https://raw.githubusercontent.com/amjadiqbal/filament-record-settings/main/art/screenshots/page-light.png)

<p align="center">
  <img alt="The settings modal on a table row, in dark mode" src="https://raw.githubusercontent.com/amjadiqbal/filament-record-settings/main/art/screenshots/action-dark.png" width="100%">
</p>

## What it does

- **A settings page** (`RecordSettingsPage`). With only a `form()` it edits the signed-in user's settings, so a
  preferences page is one small class. Point `getSettingsRecord()` at a tenant, a team or any other model instead.
- **A modal action** (`EditRecordSettingsAction`) for table rows and record pages: open a record's settings, change
  them, save.
- **Fields inside a resource form** (`->recordSetting()`): settings next to the record's own fields, saved on create
  and on edit.
- **Two backends, picked for you.** A model that uses glorand/laravel-model-settings goes through glorand, with its
  defaults, its `$settingsRules` and whichever driver it uses (field, table, Redis, cache). Any other model keeps its
  settings in a JSON column (`settings` unless you name another one).
- **Only the keys the form shows are written.** Other top-level settings in the same column or store are kept. A
  field that holds an array (a repeater, a key-value field) replaces that key as a whole.
- **Policies.** When the record's policy has an `updateSettings` method, the page and the action ask it. The ability
  name is configurable. Without such a method the plugin adds no check (see [Policies](#policies)).
- **Plain Filament UI.** Only core components, so it looks right in any panel and theme, light and dark, with no
  stylesheet or build step.

## Requirements

| | Version |
|---|---|
| PHP | 8.2+ |
| Laravel | 12 or 13 |
| Filament | 4 or 5 |
| glorand/laravel-model-settings | optional: 8 or 9 |

## Installation

```bash
composer require amjadiqbal/filament-record-settings
```

There is nothing to publish. If you want glorand's storage drivers, install and set up
[glorand/laravel-model-settings](https://github.com/glorand/laravel-model-settings#installation) as its README says.
Without it, add a JSON column to the model's table:

```php
$table->json('settings')->nullable();
```

and cast it on the model:

```php
protected function casts(): array
{
    return ['settings' => 'array'];
}
```

## Usage

### A settings page

```bash
php artisan make:filament-record-settings-page ManagePreferences
```

This writes `app/Filament/Pages/ManagePreferences.php` (or the panel's page directory; pass `--panel=` to choose,
and `--force` to overwrite an existing file).
Fill in the form:

```php
use AmjadIqbal\FilamentRecordSettings\Pages\RecordSettingsPage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ManagePreferences extends RecordSettingsPage
{
    protected static ?string $title = 'Preferences';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('theme')->options(['system' => 'System', 'light' => 'Light', 'dark' => 'Dark']),
            Toggle::make('email_digest'),
        ]);
    }
}
```

That page edits the signed-in user's settings. For another record, override `getSettingsRecord()`:

```php
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

public static function getSettingsRecord(): ?Model
{
    return Filament::getTenant();
}
```

Options on the page class:

```php
protected static ?string $settingsColumn = 'preferences';       // JSON column; null = glorand or `settings`
protected static ?string $settingsAbility = 'updateSettings';   // policy method; null = no policy check

public static function getSettingsDefaults(): array             // fallback defaults, JSON column only
{
    return ['theme' => 'system'];
}
```

To open it from the user menu instead of the sidebar:

```php
use Filament\Actions\Action;

// In your panel provider
->userMenuItems([
    Action::make('preferences')
        ->label('Preferences')
        ->url(fn (): string => ManagePreferences::getUrl())
        ->icon('heroicon-o-cog-6-tooth'),
])

// In the page
public static function shouldRegisterNavigation(): bool
{
    return false;
}
```

The page has the usual hooks: `beforeFill`, `afterFill`, `beforeValidate`, `afterValidate`, `beforeSave`,
`afterSave`, plus `mutateFormDataBeforeFill()` and `mutateFormDataBeforeSave()`.

### A modal action

```php
use AmjadIqbal\FilamentRecordSettings\Actions\EditRecordSettingsAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Toggle;

public static function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->recordActions([
            EditRecordSettingsAction::make()
                ->settingsColumn('prefs')                // optional
                ->settingsDefaults(['color' => '#0d9488']) // optional fallbacks, JSON column only
                ->schema([
                    ColorPicker::make('color'),
                    Toggle::make('archived_view'),
                ]),
        ]);
}
```

It works as a header action on a view or edit page too. `->settingsAbility('manageSettings')` changes the policy
method; `->settingsAbility(null)` turns the check off. Your own `->authorize()` or `->visible()` adds to the policy
check; it does not replace it.

### Fields in a resource form

```php
use Filament\Forms\Components\Toggle;

Toggle::make('settings.email_digest')->recordSetting(),
Toggle::make('prefs.public')->recordSetting(),              // JSON column `prefs`
```

The part before the first dot is the prefix: the JSON column for the column store. On a glorand model the prefix only
groups the fields, and the value goes through glorand. On the edit page the field is filled from the stored settings
and the model's defaults; on the create page the field's own `->default()` applies. The value is written after the
record is saved, so it works on create too. A glorand `$settingsRules` failure shows under the field and nothing is
saved.

## Defaults

Values are read in this order, the later one winning:

1. the page's `getSettingsDefaults()` or the action's `->settingsDefaults()` (JSON column only),
2. the model's defaults: `getDefaultSettings()` or a `$defaultSettings` property,
3. the stored settings.

A glorand model uses glorand's own defaults (`$defaultSettings` or `config('model_settings.defaultSettings')`), and
page and action defaults are not used for it. Defaults are only shown: a default is saved when the form shows it and
the user submits the form.

## Policies

The page and the action call the record's policy when it has the ability, `updateSettings` by default:

```php
class TeamPolicy
{
    public function updateSettings(User $user, Team $team): bool
    {
        return $user->ownsTeam($team);
    }
}
```

**With no such method, the plugin adds no check of its own.** Then anyone who can open the page, see the table row
or edit the record can change its settings. The page still needs a record (a signed-in user, or whatever
`getSettingsRecord()` returns) and its usual `canAccess()`; on the action, add `->visible()` or `->authorize()`
conditions (they are added to the policy check); resource fields follow the resource's own policy. For a per-user
preferences page that is usually what you want; for team, tenant or record settings, add the policy method.

## Coming from quadrubo/filament-model-settings

This package is a separate, new implementation for Filament 4 and 5, glorand 8 and 9 and Laravel 13. The names you
used still work, so moving is mostly an import change:

| quadrubo/filament-model-settings | Record Settings |
|---|---|
| `ModelSettingsPage` + `HasModelSettings` | `RecordSettingsPage` (no interface needed) |
| `public static function getSettingRecord()` | still read; `getSettingsRecord()` is the new name, and it defaults to the signed-in user |
| `->isModelSetting()` / `->isModelSetting('prefix')` | still registered (unless another package registered it); `->recordSetting()` is the new name |
| `make:filament-model-settings-page` | `make:filament-record-settings-page` |

Differences: the page form uses Filament 4/5's `Schema`, and saving writes only the keys in the form.

## Static analysis

`recordSetting()` and `isModelSetting()` are macros on Filament's `Field`, so PHPStan doesn't know them. Ignore the
call in your `phpstan.neon`:

```neon
parameters:
    ignoreErrors:
        - '#Call to an undefined method .*::(recordSetting|isModelSetting)\(\)#'
```

## Testing

```bash
composer test
composer analyse
scripts/ci-local.sh   # the full PHP x Laravel x Filament x glorand matrix
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## Support

- 🐛 **Bug or feature request**: [open an issue](https://github.com/amjadiqbal/filament-record-settings/issues)
- 🔒 **Security issue**: please report it privately through GitHub's security tab

## Need this customised, or something built?

I'm available for custom development, package integration, and technical consulting.

[![Hire me on Upwork](https://img.shields.io/badge/Hire%20me-Upwork-6FDA44?style=flat-square&logo=upwork&logoColor=white)](https://www.upwork.com/freelancers/amjadkhatri)

## More packages

This is part of a family of open-source packages. See [all of them](https://github.com/amjadiqbal?tab=repositories).

| | |
|---|---|
| [filament-stat-cards](https://github.com/amjadiqbal/filament-stat-cards) | Progress bars, icon tiles, colours and trends for Filament stats |
| [filament-deleted-records](https://github.com/amjadiqbal/filament-deleted-records) | A recycle bin for Filament: browse, restore or purge deleted models |
| [filament-change-requests](https://github.com/amjadiqbal/filament-change-requests) | Hold record edits in Filament for approval, with a field-by-field diff |

## Credits

- [Amjad Iqbal](https://github.com/amjadiqbal)
- The idea and the `getSettingRecord()` / `isModelSetting()` names follow
  [quadrubo/filament-model-settings](https://github.com/Quadrubo/filament-model-settings) (MIT). No code is shared, and
  this package is not affiliated with or endorsed by its author.
- Storage: [glorand/laravel-model-settings](https://github.com/glorand/laravel-model-settings).

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).
