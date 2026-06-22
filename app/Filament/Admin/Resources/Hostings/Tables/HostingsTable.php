<?php

namespace App\Filament\Admin\Resources\Hostings\Tables;

use App\Models\Hosting;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class HostingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('hosting_name')
                    ->label('Tên hosting')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->primary_name
                        ? 'Domain chính: ' . $record->primary_name
                        : null),

                TextColumn::make('customer.name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('hosting_type')
                    ->label('Loại')
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->is_expired)
                            return 'Hết hạn';
                        if ($record->is_expiring_soon)
                            return 'Sắp hết hạn';
                        return $state;
                    })
                    ->color(function ($state, $record) {
                        if ($record->is_expired)
                            return 'danger';
                        if ($record->is_expiring_soon)
                            return 'warning';
                        return match ($state) {
                            'Hoạt động' => 'success',
                            'Ngừng hoạt động' => 'gray',
                            'Hết hạn' => 'danger',
                            'Tạm dừng' => 'warning',
                            default => 'gray',
                        };
                    }),

                TextColumn::make('selling_price')
                    ->label('Giá bán')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' đ')
                    ->sortable()
                    ->alignment('right'),

                TextColumn::make('provider_name')
                    ->label('Nhà cung cấp')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('purchase_price')
                    ->label('Giá mua')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' đ')
                    ->sortable()
                    ->alignment('right')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('profit')
                    ->label('Lợi nhuận')
                    ->getStateUsing(
                        fn($record) =>
                        ($record->selling_price ?? 0) - ($record->purchase_price ?? 0)
                    )
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' đ')
                    ->color(fn($state) => $state > 0 ? 'success' : 'danger')
                    ->alignment('right')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('profit_percent')
                    ->label('% Lợi nhuận')
                    ->getStateUsing(
                        fn($record) =>
                        $record->purchase_price > 0
                        ? round((($record->selling_price - $record->purchase_price) / $record->purchase_price) * 100, 1)
                        : 0
                    )
                    ->suffix('%')
                    ->color(fn($state) => $state > 0 ? 'success' : 'danger')
                    ->alignment('right')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('billing_cycle')
                    ->label('Chu kỳ')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('expires_at')
                    ->label('Ngày gia hạn')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—')
                    ->color(fn($record) =>
                        $record->is_expired ? 'danger'
                        : ($record->is_expiring_soon ? 'warning' : null)),

                IconColumn::make('auto_renew')
                    ->label('Tự động gia hạn')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->color(fn($state) => $state ? 'success' : 'gray'),

                TextColumn::make('server.name')
                    ->label('Máy chủ')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('hosting_type')
                    ->label('Loại hosting')
                    ->options([
                        'VPS' => 'VPS',
                        'Shared' => 'Shared',
                        'Dedicated' => 'Dedicated',
                        'Cloud' => 'Cloud',
                        'Khác' => 'Khác',
                    ]),

                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Hoạt động' => 'Hoạt động',
                        'Ngừng hoạt động' => 'Ngừng hoạt động',
                        'Hết hạn' => 'Hết hạn',
                        'Tạm dừng' => 'Tạm dừng',
                    ]),

                SelectFilter::make('provider_name')
                    ->label('Nhà cung cấp')
                    ->options(fn() => Hosting::whereNotNull('provider_name')
                        ->distinct()
                        ->pluck('provider_name', 'provider_name')),

                Filter::make('expiring_soon')
                    ->label('Sắp hết hạn (30 ngày)')
                    ->query(fn(Builder $query) => $query
                        ->where('status', 'Hoạt động')
                        ->whereNotNull('expires_at')
                        ->whereDate('expires_at', '>=', now())
                        ->whereDate('expires_at', '<=', now()->addDays(30)))
                    ->toggle(),

                Filter::make('auto_renew')
                    ->label('Tự động gia hạn')
                    ->query(fn(Builder $query) => $query->where('auto_renew', true))
                    ->toggle(),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('renew')
                    ->label('Gia hạn')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalDescription('Xác nhận gia hạn hosting thêm 1 chu kỳ?')
                    ->action(function ($record) {
                        $months = match ($record->billing_cycle) {
                            'Hàng tháng' => 1,
                            'Nửa năm' => 6,
                            'Hàng năm' => 12,
                            default => 12,
                        };
                        $record->update([
                            'expires_at' => $record->expires_at
                                ? $record->expires_at->addMonths($months)
                                : now()->addMonths($months),
                            'status' => 'Hoạt động',
                        ]);
                        Notification::make()
                            ->success()
                            ->title('Đã gia hạn hosting thêm ' . $months . ' tháng')
                            ->send();
                    }),

                RestoreAction::make(),
                ForceDeleteAction::make()
                    ->visible(fn() => Auth::user()->hasRole('super_admin')),
                EditAction::make()
                    ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])),
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
            ->emptyStateHeading('Chưa có hosting nào')
            ->emptyStateIcon('heroicon-o-server');
    }
}
