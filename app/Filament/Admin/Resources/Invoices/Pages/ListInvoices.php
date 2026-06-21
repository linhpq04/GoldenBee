<?php

namespace App\Filament\Admin\Resources\Invoices\Pages;

use App\Filament\Admin\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Builder;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

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
                ->badge(Invoice::count()),

            'draft' => Tab::make('Bản nháp')
                ->badge(Invoice::where('status', 'Bản nháp')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Bản nháp')),

            'sent' => Tab::make('Đã gửi')
                ->badge(Invoice::where('status', 'Đã gửi')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Đã gửi')),

            'paid' => Tab::make('Đã thanh toán')
                ->badge(Invoice::where('status', 'Đã thanh toán')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Đã thanh toán')),

            'overdue' => Tab::make('Quá hạn')
                ->badge(Invoice::whereNotIn('status', ['Đã thanh toán', 'Đã hủy'])
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', now())
                    ->count())
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->whereNotIn('status', ['Đã thanh toán', 'Đã hủy'])
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', now())),

            'cancelled' => Tab::make('Đã hủy')
                ->badge(Invoice::where('status', 'Đã hủy')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Đã hủy')),
        ];
    }
}
