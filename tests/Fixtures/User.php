<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Tests\Fixtures;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Settings in a plain JSON column, no glorand.
 *
 * @property int $id
 * @property string $name
 * @property array<string, mixed> | null $settings
 */
class User extends Authenticatable implements FilamentUser
{
    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    /**
     * @var array<string, mixed>
     */
    public array $defaultSettings = ['theme' => 'system', 'email_digest' => true];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
