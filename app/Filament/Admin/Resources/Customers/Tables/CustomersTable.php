<?php

namespace App\Filament\Admin\Resources\Customers\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã KH')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Tên khách hàng')
                    ->description(fn($record) => $record->company_name)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Loại')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Công ty' => 'info',
                        'Cá nhân' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->label('Điện thoại')
                    ->searchable(),

                TextColumn::make('source')
                    ->label('Nguồn')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Đang hoạt động' => 'success',
                        'Tiềm năng' => 'warning',
                        'Ngừng hoạt động' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Bản ghi đã xoá'),
                SelectFilter::make('type')
                    ->label('Loại khách hàng')
                    ->options([
                        'Cá nhân' => 'Cá nhân',
                        'Công ty' => 'Công ty',
                    ]),

                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Tiềm năng' => 'Tiềm năng',
                        'Đang hoạt động' => 'Đang hoạt động',
                        'Ngừng hoạt động' => 'Ngừng hoạt động',
                    ]),

                SelectFilter::make('source')
                    ->label('Nguồn khách hàng')
                    ->options(
                        fn() => \App\Models\Customer::query()
                            ->whereNotNull('source')
                            ->distinct()
                            ->pluck('source', 'source')
                            ->toArray()
                    ),

                Filter::make('has_projects')
                    ->label('Có dự án')
                    ->query(fn(Builder $query) => $query->whereHas('projects'))
                    ->toggle(),

                Filter::make('has_contracts')
                    ->label('Có hợp đồng hiệu lực')
                    ->query(fn(Builder $query) => $query->whereHas('contracts'))
                    ->toggle(),

                Filter::make('has_debt')
                    ->label('Có công nợ')
                    ->query(fn(Builder $query) => $query->whereHas('invoices', fn($q) => $q->where('status', '!=', 'Đã thanh toán')))
                    ->toggle(),
            ])
            ->recordActions([
                Action::make('care_history')
                    ->label('Lịch sử chăm sóc')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('gray')
                    ->url(fn($record) => '#'),

                EditAction::make(),

                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}