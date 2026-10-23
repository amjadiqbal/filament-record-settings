# Changelog

All notable changes to `amjadiqbal/filament-record-settings` are documented in this file.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project uses
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.2] - 2026-10-23

### Fixed

- `->recordSetting()` fields with a nested name (`settings.notify.email`) are filled from glorand and the JSON column,
  and saved inside their key, keeping its other values. Before, they were not filled on glorand models and the JSON
  column stored a literal `notify.email` key.

### Documentation

- README: what a glorand rule failure on a `recordSetting()` field does and does not save; glorand's config defaults
  are keyed by table; Laravel 12 and 13 in the migration section.

## [1.0.1] - 2026-10-23

### Fixed

- `EditRecordSettingsAction`: your own `->authorize()` no longer replaces the `updateSettings` policy check; both
  must pass.
- `->recordSetting()` fields: a glorand `$settingsRules` failure shows under the field and the save stops, instead of
  an error with no field.

### Documentation

- README: the order defaults are read in, that glorand models ignore page and action defaults, how `recordSetting()`
  fields are filled on create and edit, that array values replace their key as a whole, what happens with no policy
  method, the command's `--force`, and the screenshot's alt text.

## [1.0.0] - 2026-10-23

### Added

- `RecordSettingsPage`: a settings page for one record, the signed-in user by default.
- `EditRecordSettingsAction`: a settings modal for table rows and record pages.
- `->recordSetting()` field macro for settings inside resource forms, on create and edit.
- Storage through glorand/laravel-model-settings 8 or 9 (any driver) or a JSON column.
- `updateSettings` policy check, configurable per page and action.
- `make:filament-record-settings-page` command.
- `getSettingRecord()` and `->isModelSetting()` still work for pages moved from quadrubo/filament-model-settings.

[Unreleased]: https://github.com/amjadiqbal/filament-record-settings/compare/v1.0.2...HEAD
[1.0.2]: https://github.com/amjadiqbal/filament-record-settings/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/amjadiqbal/filament-record-settings/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/amjadiqbal/filament-record-settings/releases/tag/v1.0.0
