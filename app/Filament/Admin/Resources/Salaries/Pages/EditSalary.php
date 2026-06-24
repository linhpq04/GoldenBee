<?php

namespace App\Filament\Admin\Resources\Salaries\Pages;

use App\Filament\Admin\Resources\Salaries\SalaryResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSalary extends EditRecord
{
    protected static string $resource = SalaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Duyệt')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn() => $this->record->status === 'Chờ duyệt'
                    && Auth::user()->hasAnyRole(['super_admin', 'admin']))
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => 'Đã duyệt',
                        'approved_by' => Auth::id(),
                    ]);
                    Notification::make()->success()->title('Đã duyệt phiếu lương')->send();
                    $this->refreshFormData(['status']);
                }),

            Action::make('mark_paid')
                ->label('Đánh dấu đã trả')
                ->icon('heroicon-o-banknotes')
                ->color('info')
                ->visible(fn() => $this->record->status === 'Đã duyệt'
                    && Auth::user()->hasAnyRole(['super_admin', 'admin']))
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'Đã thanh toán']);
                    Notification::make()->success()->title('Đã đánh dấu đã trả lương')->send();
                    $this->refreshFormData(['status']);
                }),

            Action::make('reject')
                ->label('Từ chối')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() => $this->record->status === 'Chờ duyệt'
                    && Auth::user()->hasAnyRole(['super_admin', 'admin']))
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'Từ chối']);
                    Notification::make()->warning()->title('Đã từ chối phiếu lương')->send();
                    $this->refreshFormData(['status']);
                }),

            DeleteAction::make()
                ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])),
        ];
    }

    protected function getFormActions(): array
    {
        if ($this->record->status === 'Đã thanh toán') {
            return [];
        }

        return parent::getFormActions();
    }
}
