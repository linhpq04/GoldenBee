<?php

namespace App\Filament\Admin\Resources\Contracts\Tables;

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

class ContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã hợp đồng')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('project.name')
                    ->label('Dự án')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('parent.code')
                    ->label('Hợp đồng cha')
                    ->placeholder('—'),

                TextColumn::make('customer.name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Loại hợp đồng')
                    ->badge()
                    ->color(fn($state) => 'warning'),

                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('contract_value')
                    ->label('Giá trị hợp đồng')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' đ')
                    ->sortable()
                    ->alignment('right'),

                IconColumn::make('has_vat')
                    ->label('Có VAT')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->color(fn($state) => $state ? 'success' : 'danger'),

                TextColumn::make('vat_percent')
                    ->label('% VAT')
                    ->formatStateUsing(fn($state) => $state . '%')
                    ->sortable()
                    ->alignment('right'),

                TextColumn::make('total')
                    ->label('Tổng tiền')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' đ')
                    ->sortable()
                    ->alignment('right'),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'Bản nháp', 'draft' => 'gray',
                        'Chờ ký', 'pending' => 'warning',
                        'Đang hiệu lực', 'active' => 'success',
                        'Hết hạn', 'Đã hủy' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(function ($state, $record) {
                        if ($state === 'Đang hiệu lực' && $record->is_expired) {
                            return 'Hết hạn';
                        }
                        return $state;
                    }),

                TextColumn::make('signed_at')
                    ->label('Ngày ký')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('start_date')
                    ->label('Ngày bắt đầu')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('end_date')
                    ->label('Ngày kết thúc')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('file_path')
                    ->label('File hợp đồng')
                    ->placeholder('—')
                    ->limit(20),

                TextColumn::make('createdBy.name')
                    ->label('Người tạo')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Cập nhật lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Bản nháp' => 'Bản nháp',
                        'Chờ ký' => 'Chờ ký',
                        'Đang hiệu lực' => 'Đang hiệu lực',
                        'Hết hạn' => 'Hết hạn',
                        'Đã hủy' => 'Đã hủy',
                    ]),

                SelectFilter::make('type')
                    ->label('Loại hợp đồng')
                    ->options([
                        'Hợp đồng dịch vụ' => 'Hợp đồng dịch vụ',
                        'Hợp đồng bảo trì' => 'Hợp đồng bảo trì',
                        'Hợp đồng tư vấn' => 'Hợp đồng tư vấn',
                        'Hợp đồng mua bán' => 'Hợp đồng mua bán',
                        'Hợp đồng thuê' => 'Hợp đồng thuê',
                        'Khác' => 'Khác',
                    ]),

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
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Chưa có hợp đồng nào')
            ->emptyStateIcon('heroicon-o-document-check');
    }
}
