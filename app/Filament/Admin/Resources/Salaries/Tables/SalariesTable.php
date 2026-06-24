<?php

namespace App\Filament\Admin\Resources\Salaries\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SalariesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nhân viên')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('salary_type')
                    ->label('Loại lương')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('period')
                    ->label('Kỳ lương')
                    ->placeholder('—'),

                TextColumn::make('payment_date')
                    ->label('Ngày thanh toán')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('base_salary')
                    ->label('Số tiền cơ bản')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' đ')
                    ->sortable(),

                TextColumn::make('net_salary')
                    ->label('Thực nhận')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' đ')
                    ->sortable()
                    ->color('success'),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Chờ duyệt' => 'warning',
                        'Đã duyệt' => 'info',
                        'Đã thanh toán' => 'success',
                        'Từ chối' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('project.name')
                    ->label('Dự án')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('work_hours')
                    ->label('Số giờ')
                    ->suffix(' giờ')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('payment_method')
                    ->label('Hình thức')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approvedBy.name')
                    ->label('Người duyệt')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('salary_type')
                    ->label('Loại lương')
                    ->options([
                        'Lương tháng cố định' => 'Lương tháng cố định',
                        'Lương theo giờ' => 'Lương theo giờ',
                        'Hoa hồng' => 'Hoa hồng',
                        'Thưởng' => 'Thưởng',
                        'Tạm ứng' => 'Tạm ứng',
                        'Khác' => 'Khác',
                    ]),

                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Chờ duyệt' => 'Chờ duyệt',
                        'Đã duyệt' => 'Đã duyệt',
                        'Đã thanh toán' => 'Đã thanh toán',
                        'Từ chối' => 'Từ chối',
                    ]),

                SelectFilter::make('payment_method')
                    ->label('Hình thức')
                    ->options([
                        'Tiền mặt' => 'Tiền mặt',
                        'Chuyển khoản' => 'Chuyển khoản',
                        'Khác' => 'Khác',
                    ]),

                SelectFilter::make('user_id')
                    ->label('Nhân viên')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('payment_date')
                    ->form([
                        DatePicker::make('from')->label('Từ ngày')->displayFormat('d/m/Y'),
                        DatePicker::make('to')->label('Đến ngày')->displayFormat('d/m/Y'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('payment_date', '>=', $data['from']))
                            ->when($data['to'], fn($q) => $q->whereDate('payment_date', '<=', $data['to']));
                    })
                    ->label('Khoảng thời gian'),

            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Duyệt')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'Chờ duyệt'
                        && Auth::user()->hasAnyRole(['super_admin', 'admin']))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'Đã duyệt',
                            'approved_by' => Auth::id(),
                        ]);
                        Notification::make()->success()->title('Đã duyệt phiếu lương')->send();
                    }),

                Action::make('mark_paid')
                    ->label('Đã trả')
                    ->icon('heroicon-o-banknotes')
                    ->color('info')
                    ->visible(fn($record) => $record->status === 'Đã duyệt'
                        && Auth::user()->hasAnyRole(['super_admin', 'admin']))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'Đã thanh toán']);
                        Notification::make()->success()->title('Đã đánh dấu đã trả lương')->send();
                    }),

                EditAction::make()->label('Chỉnh sửa'),

                Action::make('reject')
                    ->label('Từ chối')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'Chờ duyệt'
                        && Auth::user()->hasAnyRole(['super_admin', 'admin']))
                    ->requiresConfirmation()
                    ->modalDescription('Xác nhận từ chối phiếu lương này?')
                    ->action(function ($record) {
                        $record->update(['status' => 'Từ chối']);
                        Notification::make()->warning()->title('Đã từ chối phiếu lương')->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])),
                ]),
            ])
            ->defaultSort('payment_date', 'desc')
            ->emptyStateHeading('Không có phiếu lương nào')
            ->emptyStateIcon('heroicon-o-banknotes');
    }
}
