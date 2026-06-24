<?php

namespace App\Filament\Admin\Resources\Salaries\Pages;

use App\Filament\Admin\Resources\Salaries\SalaryResource;
use App\Models\SalaryPayment;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Builder;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListSalaries extends ListRecords
{
    protected static string $resource = SalaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tất cả')
                ->badge(SalaryPayment::count()),

            'pending' => Tab::make('Chờ duyệt')
                ->badge(SalaryPayment::where('status', 'Chờ duyệt')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Chờ duyệt')),

            'approved' => Tab::make('Đã duyệt')
                ->badge(SalaryPayment::where('status', 'Đã duyệt')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Đã duyệt')),

            'paid' => Tab::make('Đã thanh toán')
                ->badge(SalaryPayment::where('status', 'Đã thanh toán')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Đã thanh toán')),

            'rejected' => Tab::make('Từ chối')
                ->badge(SalaryPayment::where('status', 'Từ chối')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Từ chối')),
        ];
    }
}
