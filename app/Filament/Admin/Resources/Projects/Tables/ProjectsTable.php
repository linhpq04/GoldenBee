<?php

namespace App\Filament\Admin\Resources\Projects\Tables;

use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã dự án')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('customer.name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('name')
                    ->label('Tên dự án')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('manager.name')
                    ->label('Người quản lý')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Loại')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'Lên kế hoạch' => 'gray',
                        'Đang thực hiện' => 'info',
                        'Tạm dừng' => 'warning',
                        'Hoàn thành' => 'success',
                        'Đã hủy' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->is_overdue) {
                            return $state . ' ⚠️';
                        }
                        return $state;
                    }),

                TextColumn::make('priority')
                    ->label('Ưu tiên')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'Thấp' => 'gray',
                        'Trung bình' => 'info',
                        'Cao' => 'warning',
                        'Khẩn cấp' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('contract_value')
                    ->label('Giá trị')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' đ')
                    ->sortable()
                    ->alignment('right'),

                IconColumn::make('warranty_lifetime')
                    ->label('BH trọn đời')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->color(fn($state) => $state ? 'success' : 'danger'),

                TextColumn::make('warranty_expires_at')
                    ->label('Hết hạn BH')
                    ->date('d/m/Y')
                    ->placeholder('Trọn đời')
                    ->color(fn($state) => $state && Carbon::parse($state)->isPast() ? 'danger' : null)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('start_date')
                    ->label('Bắt đầu')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('end_date')
                    ->label('Kết thúc')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Lên kế hoạch' => 'Lên kế hoạch',
                        'Đang thực hiện' => 'Đang thực hiện',
                        'Tạm dừng' => 'Tạm dừng',
                        'Hoàn thành' => 'Hoàn thành',
                        'Đã hủy' => 'Đã hủy',
                    ]),

                SelectFilter::make('type')
                    ->label('Loại')
                    ->options([
                        'Website' => 'Website',
                        'SEO' => 'SEO',
                        'Mobile App' => 'Mobile App',
                        'Phần mềm' => 'Phần mềm',
                        'Khác' => 'Khác',
                    ]),

                SelectFilter::make('priority')
                    ->label('Ưu tiên')
                    ->options([
                        'Thấp' => 'Thấp',
                        'Trung bình' => 'Trung bình',
                        'Cao' => 'Cao',
                        'Khẩn cấp' => 'Khẩn cấp',
                    ]),

                SelectFilter::make('customer')
                    ->label('Khách hàng')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('manager')
                    ->label('Người quản lý')
                    ->relationship('manager', 'name')
                    ->searchable()
                    ->preload(),

                TrashedFilter::make(),
            ])
            ->recordActions([
                RestoreAction::make(),
                ForceDeleteAction::make()
                    ->visible(fn() => Auth::user()->hasRole('super_admin')),
                EditAction::make()
                    ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin', 'sales'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Chưa có dự án nào')
            ->emptyStateIcon('heroicon-o-briefcase');
    }
}
