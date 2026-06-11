<?php

namespace App\Filament\Admin\Resources\Supports\Pages;

use App\Filament\Admin\Resources\Supports\SupportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupports extends ListRecords
{
    protected static string $resource = SupportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
