<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Tests\Fixtures;

class TeamPolicy
{
    public static bool $canUpdateSettings = true;

    public function updateSettings(User $user, Team $team): bool
    {
        return static::$canUpdateSettings;
    }
}
