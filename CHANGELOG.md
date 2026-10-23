# Changelog

All notable changes to `amjadiqbal/filament-record-settings` are documented in this file.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project uses
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- `RecordSettingsPage`: a settings page for one record, the signed-in user by default.
- `EditRecordSettingsAction`: a settings modal for table rows and record pages.
- `->recordSetting()` field macro for settings inside resource forms, on create and edit.
- Storage through glorand/laravel-model-settings 8 or 9 (any driver) or a JSON column.
- `updateSettings` policy check, configurable per page and action.
- `make:filament-record-settings-page` command.
- `getSettingRecord()` and `->isModelSetting()` still work for pages moved from quadrubo/filament-model-settings.
