<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRecordSettings\Tests\Fixtures;

use Filament\Resources\Pages\ListRecords;

class ListOrgs extends ListRecords
{
    protected static string $resource = OrgResource::class;
}
