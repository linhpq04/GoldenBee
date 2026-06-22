<?php

namespace App\Filament\Admin\Resources\Courses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã khóa học')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('title')
                    ->label('Tên khóa học')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('format')
                    ->label('Hình thức')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        '1-1' => 'info',
                        'Nhóm' => 'warning',
                        'Trực tuyến' => 'success',
                        'Tại lớp' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'Bản nháp' => 'gray',
                        'Đang mở' => 'success',
                        'Hoàn thành' => 'info',
                        'Đã hủy' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('enrollments_count')
                    ->label('Số học viên')
                    ->counts('enrollments')
                    ->suffix(fn($record) => $record->max_student
                        ? '/' . $record->max_student
                        : '')
                    ->alignment('right'),

                TextColumn::make('duration_hours')
                    ->label('Thời lượng')
                    ->suffix('h')
                    ->alignment('right')
                    ->placeholder('—'),

                TextColumn::make('price')
                    ->label('Học phí')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' đ')
                    ->sortable()
                    ->alignment('right'),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('format')
                    ->label('Hình thức')
                    ->options([
                        '1-1' => '1-1 (Cá nhân)',
                        'Nhóm' => 'Nhóm',
                        'Trực tuyến' => 'Trực tuyến',
                        'Tại lớp' => 'Tại lớp',
                    ]),

                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Bản nháp' => 'Bản nháp',
                        'Đang mở' => 'Đang mở',
                        'Hoàn thành' => 'Hoàn thành',
                        'Đã hủy' => 'Đã hủy',
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
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
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Không có Khóa học nào')
            ->emptyStateIcon('heroicon-o-academic-cap');
    }
}
