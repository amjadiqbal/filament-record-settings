<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Tests;

use AmjadIqbal\FilamentRecordSettings\FilamentRecordSettingsServiceProvider;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\AdminPanelProvider;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\Project;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\ProjectPolicy;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\Team;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\TeamPolicy;
use AmjadIqbal\FilamentRecordSettings\Tests\Fixtures\User;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Glorand\Model\Settings\ModelSettingsServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Kirschbaum\PowerJoins\PowerJoinsServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Team::class, TeamPolicy::class);
        ProjectPolicy::$canUpdateSettings = true;
        TeamPolicy::$canUpdateSettings = true;
    }

    protected function getPackageProviders($app): array
    {
        $providers = [
            BladeIconsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            PowerJoinsServiceProvider::class,
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            ActionsServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            NotificationsServiceProvider::class,
            'Filament\\QueryBuilder\\QueryBuilderServiceProvider',
            SchemasServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            FilamentServiceProvider::class,
            ModelSettingsServiceProvider::class,
            FilamentRecordSettingsServiceProvider::class,
            AdminPanelProvider::class,
        ];

        return array_values(array_filter($providers, 'class_exists'));
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('auth.providers.users.model', User::class);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->json('settings')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('orgs', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // glorand's settings table (its create_settings_table stub).
        Schema::create('model_settings', function (Blueprint $table): void {
            $table->increments('id');
            $table->bigInteger('model_id');
            $table->string('model_type');
            $table->json('settings');
            $table->timestamps();
            $table->unique(['model_id', 'model_type']);
        });

        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->json('prefs')->nullable();
            $table->timestamps();
        });
    }

    protected function makeUser(string $name = 'Admin'): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => strtolower($name).'@example.test',
            'password' => 'secret-password',
        ]);
    }
}
