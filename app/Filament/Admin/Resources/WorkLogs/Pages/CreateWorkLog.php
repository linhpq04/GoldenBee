<?php

namespace App\Filament\Admin\Resources\WorkLogs\Pages;

use App\Filament\Admin\Resources\WorkLogs\WorkLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkLog extends CreateRecord
{
    protected static string $resource = WorkLogResource::class;
}
