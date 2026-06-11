<?php

namespace App\Filament\Admin\Resources\Contracts\Pages;

use App\Filament\Admin\Resources\Contracts\ContractResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContract extends CreateRecord
{
    protected static string $resource = ContractResource::class;
}
