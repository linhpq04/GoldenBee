<?php

namespace App\Filament\Admin\Resources\Invoices\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã hóa đơn')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('invoice_type')
                    ->label('Loại')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Doanh thu' => 'success',
                        'Chi phí' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('customer.name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('invoice_date')
                    ->label('Ngày hóa đơn')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->is_overdue && in_array($state, ['Bản nháp', 'Đã gửi'])) {
                            return 'Quá hạn';
                        }
                        return $state;
                    })
                    ->color(function ($state, $record) {
                        if ($record->is_overdue && in_array($state, ['Bản nháp', 'Đã gửi'])) {
                            return 'danger';
                        }
                        return match ($state) {
                            'Bản nháp' => 'gray',
                            'Đã gửi' => 'warning',
                            'Đã thanh toán' => 'success',
                            'Quá hạn' => 'danger',
                            'Đã hủy' => 'gray',
                            default => 'gray',
                        };
                    }),

                TextColumn::make('total')
                    ->label('Tổng tiền')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' đ')
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Đã thanh toán')
                    ->formatStateUsing(
                        fn($state, $record) =>
                        number_format((float) $state, 0, ',', '.') . ' / ' .
                        number_format((float) $record->total, 0, ',', '.') . ' đ'
                    ),

                TextColumn::make('due_date')
                    ->label('Hạn thanh toán')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable()
                    ->color(fn($record) => $record->is_overdue ? 'danger' : null),

                TextColumn::make('contract.title')
                    ->label('Dịch vụ liên kết')
                    ->placeholder('—')
                    ->limit(30)
                    ->description(fn($record) => $record->service_type),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('file_path')
                    ->label('File')
                    ->formatStateUsing(fn($state) => $state ? 'Tải file' : '—')
                    ->color(fn($state) => $state ? 'primary' : 'gray')
                    ->icon(fn($state) => $state ? 'heroicon-o-paper-clip' : null)
                    ->url(fn($record) => $record->file_path ? Storage::disk('public')->url($record->file_path) : null)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Bản ghi đã xoá'),

                SelectFilter::make('invoice_type')
                    ->label('Loại hóa đơn')
                    ->options([
                        'Doanh thu' => 'Doanh thu',
                        'Chi phí' => 'Chi phí',
                    ]),

                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Bản nháp' => 'Bản nháp',
                        'Đã gửi' => 'Đã gửi',
                        'Đã thanh toán' => 'Đã thanh toán',
                        'Quá hạn' => 'Quá hạn',
                        'Đã hủy' => 'Đã hủy',
                    ]),

                SelectFilter::make('payment_method')
                    ->label('Phương thức thanh toán')
                    ->options([
                        'Tiền mặt' => 'Tiền mặt',
                        'Chuyển khoản' => 'Chuyển khoản',
                        'Khác' => 'Khác',
                    ]),

                SelectFilter::make('customer_id')
                    ->label('Khách hàng')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('mark_paid')
                    ->label('Đánh dấu đã TT')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => !$record->is_fully_paid && $record->status !== 'Đã hủy')
                    ->requiresConfirmation()
                    ->modalDescription('Xác nhận hóa đơn này đã được thanh toán đủ?')
                    ->action(function ($record) {
                        $record->update([
                            'paid_amount' => $record->total,
                            'paid_date' => now(),
                            'status' => 'Đã thanh toán',
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Đã đánh dấu thanh toán')
                            ->send();
                    }),
                EditAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
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
            ->defaultSort('invoice_date', 'desc')
            ->emptyStateHeading('Không có hóa đơn nào')
            ->emptyStateIcon('heroicon-o-receipt-percent');
    }
}
