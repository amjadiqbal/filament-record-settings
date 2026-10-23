<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('generates a settings page in the panel page directory', function (): void {
    $path = app_path('Filament/Pages/ManagePreferences.php');
    File::delete($path);

    $this->artisan('make:filament-record-settings-page', ['name' => 'ManagePreferences'])->assertSuccessful();

    expect(File::get($path))
        ->toContain('class ManagePreferences extends RecordSettingsPage')
        ->toContain('namespace App\Filament\Pages;');

    $this->artisan('make:filament-record-settings-page', ['name' => 'ManagePreferences'])->assertFailed();

    File::delete($path);
});
