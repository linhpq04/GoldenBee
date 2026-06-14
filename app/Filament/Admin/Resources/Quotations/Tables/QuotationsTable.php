<?php

namespace App\Filament\Admin\Resources\Quotations\Tables;

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
use Illuminate\Database\Eloquent\Builder;

class QuotationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã báo giá')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('version')
                    ->label('Phiên bản')
                    ->formatStateUsing(fn($state) => 'v' . $state),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Bản nháp' => 'gray',
                        'Chờ duyệt' => 'warning',
                        'Đã gửi' => 'info',
                        'Chấp nhận' => 'success',
                        'Từ chối' => 'danger',
                        'Đã chuyển DA' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(function ($state, $record) {
                        if ($state === 'Bản nháp' || $state === 'Chờ duyệt' || $state === 'Đã gửi') {
                            if ($record->is_expired) {
                                return 'Hết hạn';
                            }
                        }
                        return $state;
                    }),

                TextColumn::make('total')
                    ->label('Tổng tiền')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' đ')
                    ->sortable(),

                TextColumn::make('valid_until')
                    ->label('Hiệu lực')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('projects.name')
                    ->label('Dự án')
                    ->limit(30)
                    ->default('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('createdBy.name')
                    ->label('Người tạo')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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

                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Bản nháp' => 'Bản nháp',
                        'Chờ duyệt' => 'Chờ duyệt',
                        'Đã gửi' => 'Đã gửi',
                        'Chấp nhận' => 'Chấp nhận',
                        'Từ chối' => 'Từ chối',
                        'Đã chuyển DA' => 'Đã chuyển DA',
                    ]),

                SelectFilter::make('customer_id')
                    ->label('Khách hàng')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('tinh_trang')
                    ->label('Tình trạng')
                    ->options([
                        'con_hieu_luc' => 'Còn hiệu lực',
                        'het_han' => 'Hết hạn',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'het_han') {
                            $query->whereNotIn('status', ['Chấp nhận', 'Đã chuyển DA'])
                                ->whereNotNull('valid_until')
                                ->whereDate('valid_until', '<', now());
                        } elseif ($data['value'] === 'con_hieu_luc') {
                            $query->where(function ($q) {
                                $q->whereNull('valid_until')
                                    ->orWhereDate('valid_until', '>=', now());
                            });
                        }
                    }),
            ])
            ->recordActions([
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
