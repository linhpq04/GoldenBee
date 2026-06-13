<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Họ và tên')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->label('Số điện thoại')
                    ->icon('heroicon-o-phone')
                    ->searchable(),

                TextColumn::make('position')
                    ->label('Chức vụ')
                    ->badge()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Hoạt động' => 'success',
                        'Không hoạt động' => 'gray',
                        'Đình chỉ' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('base_salary')
                    ->label('Lương cơ bản')
                    ->default(0)
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' đ')
                    ->sortable(),

                TextColumn::make('joined_at')
                    ->label('Ngày vào làm')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bank_account_number')
                    ->label('Số TK ngân hàng')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bank_name')
                    ->label('Ngân hàng')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tax_code')
                    ->label('Mã số thuế')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Hoạt động' => 'Hoạt động',
                        'Không hoạt động' => 'Không hoạt động',
                        'Đình chỉ' => 'Đình chỉ',
                    ]),

                SelectFilter::make('gender')
                    ->label('Giới tính')
                    ->options([
                        'Nam' => 'Nam',
                        'Nữ' => 'Nữ',
                        'Khác' => 'Khác',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->hasRole('super_admin'))
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $skippedSelf = $records->contains(fn($record) => $record->id === Auth::id());

                            $toDelete = $records->reject(fn($record) => $record->id === Auth::id());
                            $toDelete->each(fn($record) => $record->delete());

                            if ($skippedSelf) {
                                \Filament\Notifications\Notification::make()
                                    ->warning()
                                    ->title('Đã xóa ' . $toDelete->count() . ' nhân viên')
                                    ->body('Không thể xóa tài khoản của chính bạn, đã bỏ qua.')
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->success()
                                    ->title('Đã xóa ' . $toDelete->count() . ' nhân viên')
                                    ->send();
                            }
                        })
                        ->successNotification(null),
                ]),
            ]);
    }
}
