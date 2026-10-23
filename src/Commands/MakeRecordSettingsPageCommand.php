<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Commands;

use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeRecordSettingsPageCommand extends Command
{
    protected $signature = 'make:filament-record-settings-page {name : The page class, e.g. ManagePreferences} {--panel= : The panel ID} {--F|force : Overwrite an existing file}';

    protected $description = 'Create a Filament page that edits one record\'s settings (the signed-in user\'s by default)';

    public function handle(Filesystem $files): int
    {
        $panel = $this->option('panel') ? Filament::getPanel($this->option('panel')) : Filament::getDefaultPanel();

        $directory = $panel->getPageDirectories()[0] ?? app_path('Filament/Pages');
        $namespace = $panel->getPageNamespaces()[0] ?? 'App\\Filament\\Pages';

        $class = Str::studly(class_basename(str_replace('/', '\\', (string) $this->argument('name'))));
        $path = rtrim($directory, '/').'/'.$class.'.php';

        if ($files->exists($path) && ! $this->option('force')) {
            $this->components->error("[{$path}] already exists. Use --force to overwrite it.");

            return self::FAILURE;
        }

        $stub = (string) file_get_contents(__DIR__.'/../../stubs/RecordSettingsPage.stub');

        $files->ensureDirectoryExists(dirname($path));
        $files->put($path, str_replace(['{{ namespace }}', '{{ class }}'], [$namespace, $class], $stub));

        $this->components->info("Settings page [{$path}] created.");

        return self::SUCCESS;
    }
}
