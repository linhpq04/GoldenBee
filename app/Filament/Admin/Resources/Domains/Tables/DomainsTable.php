<?php

namespace App\Filament\Admin\Resources\Domains\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class DomainsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('domain_name')
                    ->label('Tên miền')
                    ->searchable()
                    ->sortable()
                    ->url(fn($record) => 'https://' . $record->domain_name)
                    ->openUrlInNewTab(),

                TextColumn::make('customer.name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('provider_name')
                    ->label('Nhà cung cấp')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->is_overdue && $state === 'Hoạt động') {
                            return 'Hết hạn';
                        }
                        if ($record->is_expiring_soon && $state === 'Hoạt động') {
                            return 'Sắp hết hạn';
                        }
                        return $state;
                    })
                    ->color(function ($state, $record) {
                        if ($record->is_overdue)
                            return 'danger';
                        if ($record->is_expiring_soon)
                            return 'warning';
                        return match ($state) {
                            'Hoạt động' => 'success',
                            'Ngừng hoạt động' => 'gray',
                            'Hết hạn' => 'danger',
                            'Đang chuyển' => 'info',
                            default => 'gray',
                        };
                    }),

                TextColumn::make('expires_at')
                    ->label('Ngày hết hạn')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn($record) => $record->is_overdue ? 'danger' : ($record->is_expiring_soon ? 'warning' : null)),

                IconColumn::make('auto_renew')
                    ->label('Tự động gia hạn')
                    ->boolean(),

                TextColumn::make('selling_price')
                    ->label('Giá bán')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' đ')
                    ->sortable(),

                TextColumn::make('project.name')
                    ->label('Dự án')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Bản ghi đã xoá'),

                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Hoạt động' => 'Hoạt động',
                        'Ngừng hoạt động' => 'Ngừng hoạt động',
                        'Hết hạn' => 'Hết hạn',
                        'Đang chuyển' => 'Đang chuyển',
                    ]),

                Filter::make('expiring_soon')
                    ->label('Sắp hết hạn (30 ngày)')
                    ->query(
                        fn(Builder $query) => $query
                            ->where('status', 'Hoạt động')
                            ->whereNotNull('expires_at')
                            ->whereDate('expires_at', '>=', now())
                            ->whereDate('expires_at', '<=', now()->addDays(30))
                    )
                    ->toggle(),

                Filter::make('auto_renew')
                    ->label('Tự động gia hạn')
                    ->query(fn(Builder $query) => $query->where('auto_renew', true))
                    ->toggle(),
            ])
            ->recordActions([
                Action::make('renew')
                    ->label('Gia hạn 1 năm')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalDescription('Xác nhận gia hạn tên miền thêm 1 năm?')
                    ->action(function ($record) {
                        $record->update([
                            'expires_at' => $record->expires_at
                                ? $record->expires_at->addYear()
                                : now()->addYear(),
                            'last_renewed_at' => now(),
                            'status' => 'Hoạt động',
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Đã gia hạn tên miền thêm 1 năm')
                            ->send();
                    }),

                EditAction::make()->label('Chỉnh sửa'),
                RestoreAction::make(),
                ForceDeleteAction::make()
                    ->visible(fn() => Auth::user()->hasRole('super_admin')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->hasRole('super_admin')),
                ]),
            ])
            ->defaultSort('expires_at', 'asc')
            ->emptyStateHeading('Không có tên miền nào')
            ->emptyStateIcon('heroicon-o-globe-alt');
    }
}
