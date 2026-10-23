<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Tests\Fixtures;

class ProjectPolicy
{
    public static bool $canUpdateSettings = true;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Project $project): bool
    {
        return true;
    }

    public function updateSettings(User $user, Project $project): bool
    {
        return static::$canUpdateSettings;
    }
}
