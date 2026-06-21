<?php

namespace App\Filament\Admin\Resources\Tasks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->limit(40)
                    ->description(fn($record) => $record->parentTask?->title
                        ? 'Sub-task của: ' . $record->parentTask->title
                        : null),

                TextColumn::make('project.name')
                    ->label('Dự án')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('assignee.name')
                    ->label('Người thực hiện')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'Chưa làm' => 'gray',
                        'Đang làm' => 'info',
                        'Đang review' => 'warning',
                        'Hoàn thành' => 'success',
                        'Tạm dừng' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('priority')
                    ->label('Độ ưu tiên')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'Thấp' => 'gray',
                        'Trung bình' => 'info',
                        'Cao' => 'warning',
                        'Khẩn cấp' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('estimated_hours')
                    ->label('Giờ ước tính')
                    ->suffix('h')
                    ->sortable()
                    ->alignment('right')
                    ->placeholder('—'),

                TextColumn::make('logged_hours')
                    ->label('Đã log')
                    ->getStateUsing(fn($record) => $record->workLogs->sum('hours'))
                    ->suffix('h')
                    ->alignment('right')
                    ->color(fn($record) => $record->estimated_hours > 0 &&
                        $record->workLogs->sum('hours') > $record->estimated_hours
                        ? 'danger' : null),

                TextColumn::make('due_date')
                    ->label('Hạn hoàn thành')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—')
                    ->color(fn($record) =>
                        $record->due_date &&
                        $record->due_date->isPast() &&
                        $record->status !== 'Hoàn thành'
                        ? 'danger' : null),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Chưa làm' => 'Chưa làm',
                        'Đang làm' => 'Đang làm',
                        'Đang review' => 'Đang review',
                        'Hoàn thành' => 'Hoàn thành',
                        'Tạm dừng' => 'Tạm dừng',
                    ]),

                SelectFilter::make('priority')
                    ->label('Độ ưu tiên')
                    ->options([
                        'Thấp' => 'Thấp',
                        'Trung bình' => 'Trung bình',
                        'Cao' => 'Cao',
                        'Khẩn cấp' => 'Khẩn cấp',
                    ]),

                SelectFilter::make('project')
                    ->label('Dự án')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('assignee')
                    ->label('Người thực hiện')
                    ->relationship('assignee', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin', 'sales'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_date', 'asc')
            ->emptyStateHeading('Không có Công việc nào')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }
}
