<?php

namespace App\Filament\Admin\Resources\Quotations\Pages;

use App\Filament\Admin\Resources\Quotations\QuotationResource;
use App\Models\Quotation;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListQuotations extends ListRecords
{
    protected static string $resource = QuotationResource::class;

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
                ->badge(Quotation::count()),

            'draft' => Tab::make('Bản nháp')
                ->badge(Quotation::where('status', 'Bản nháp')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Bản nháp')),

            'sent' => Tab::make('Đã gửi')
                ->badge(Quotation::where('status', 'Đã gửi')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Đã gửi')),

            'accepted' => Tab::make('Đã chấp nhận')
                ->badge(Quotation::where('status', 'Chấp nhận')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Chấp nhận')),

            'converted' => Tab::make('Đã chuyển DA')
                ->badge(Quotation::where('status', 'Đã chuyển DA')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Đã chuyển DA')),

            'rejected' => Tab::make('Từ chối')
                ->badge(Quotation::where('status', 'Từ chối')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Từ chối')),

            'expired' => Tab::make('Hết hạn')
                ->badge(Quotation::whereNotIn('status', ['Chấp nhận', 'Đã chuyển DA'])
                    ->whereNotNull('valid_until')
                    ->whereDate('valid_until', '<', now())
                    ->count())
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->whereNotIn('status', ['Chấp nhận', 'Đã chuyển DA'])
                    ->whereNotNull('valid_until')
                    ->whereDate('valid_until', '<', now())),
        ];
    }
}
