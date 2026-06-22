<?php

namespace App\Filament\Admin\Resources\Domains\Pages;

use App\Filament\Admin\Resources\Domains\DomainResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditDomain extends EditRecord
{
    protected static string $resource = DomainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RestoreAction::make(),
            DeleteAction::make()
                ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])),
            ForceDeleteAction::make()
                ->visible(fn() => Auth::user()->hasRole('super_admin')),
        ];
    }
}
