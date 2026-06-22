<?php

namespace App\Filament\Admin\Resources\Supports\Tables;

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

class SupportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã ticket')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->limit(40)
                    ->description(fn($record) => $record->category),

                TextColumn::make('customer.name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable(),

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

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'Mới' => 'info',
                        'Đang xử lý' => 'warning',
                        'Chờ phản hồi' => 'gray',
                        'Đã giải quyết' => 'success',
                        'Đóng' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('assignee.name')
                    ->label('Người xử lý')
                    ->placeholder('Chưa phân công'),

                TextColumn::make('project.name')
                    ->label('Dự án')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('ended_at')
                    ->label('Giải quyết lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Mới' => 'Mới',
                        'Đang xử lý' => 'Đang xử lý',
                        'Chờ phản hồi' => 'Chờ phản hồi',
                        'Đã giải quyết' => 'Đã giải quyết',
                        'Đóng' => 'Đóng',
                    ]),

                SelectFilter::make('priority')
                    ->label('Độ ưu tiên')
                    ->options([
                        'Thấp' => 'Thấp',
                        'Trung bình' => 'Trung bình',
                        'Cao' => 'Cao',
                        'Khẩn cấp' => 'Khẩn cấp',
                    ]),

                SelectFilter::make('assignee')
                    ->label('Người xử lý')
                    ->relationship('assignee', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('customer')
                    ->label('Khách hàng')
                    ->relationship('customer', 'name')
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
                    DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->hasRole('super_admin')),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Không có Tickets hỗ trợ nào')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }
}
