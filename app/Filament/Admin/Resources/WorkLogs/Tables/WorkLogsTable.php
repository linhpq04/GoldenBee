<?php

namespace App\Filament\Admin\Resources\WorkLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;

class WorkLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('work_date')
                    ->label('Ngày làm việc')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Nhân viên')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('task.title')
                    ->label('Công việc')
                    ->placeholder('—')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('task_type')
                    ->label('Loại công việc')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                TextColumn::make('hours')
                    ->label('Số giờ')
                    ->suffix('h')
                    ->sortable()
                    ->alignment('right'),

                TextColumn::make('description')
                    ->label('Mô tả')
                    ->limit(50)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user')
                    ->label('Nhân viên')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('task_type')
                    ->label('Loại công việc')
                    ->options([
                        'Thiết kế' => 'Thiết kế',
                        'Lập trình' => 'Lập trình',
                        'Kiểm thử' => 'Kiểm thử',
                        'Nội dung' => 'Nội dung',
                        'SEO' => 'SEO',
                        'Tư vấn' => 'Tư vấn',
                        'Họp' => 'Họp',
                        'Khác' => 'Khác',
                    ]),

                Filter::make('work_date')
                    ->label('Tháng')
                    ->form([
                        DatePicker::make('from')
                            ->label('Từ ngày')
                            ->displayFormat('d/m/Y')
                            ->default(now()->startOfMonth()),
                        DatePicker::make('until')
                            ->label('Đến ngày')
                            ->displayFormat('d/m/Y')
                            ->default(now()->endOfMonth()),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('work_date', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('work_date', '<=', $data['until']));
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])),
                ]),
            ])
            ->defaultSort('work_date', 'desc')
            ->emptyStateHeading('Không có chấm công nào')
            ->emptyStateIcon('heroicon-o-clock');
    }
}
