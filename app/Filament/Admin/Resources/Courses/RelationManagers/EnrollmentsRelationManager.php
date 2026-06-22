<?php

namespace App\Filament\Admin\Resources\Courses\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';
    protected static ?string $title = 'Danh sách học viên';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('customer_id')
                ->label('Học viên (Khách hàng)')
                ->relationship('customer', 'name')
                ->searchable()
                ->preload()
                ->required(),

            DatePicker::make('enrolled_at')
                ->label('Ngày đăng ký')
                ->displayFormat('d/m/Y')
                ->required()
                ->default(today()),

            Select::make('status')
                ->label('Trạng thái')
                ->required()
                ->options([
                    'Đang học' => 'Đang học',
                    'Hoàn thành' => 'Hoàn thành',
                    'Đã hủy' => 'Đã hủy',
                    'Tạm dừng' => 'Tạm dừng',
                ])
                ->default('Đang học'),

            TextInput::make('total_amount')
                ->label('Học phí')
                ->numeric()
                ->default(fn() => $this->getOwnerRecord()->price ?? 0)
                ->minValue(0)
                ->prefix('VNĐ')
                ->helperText('Tự động lấy từ giá khóa học'),

            TextInput::make('paid_amount')
                ->label('Đã thanh toán')
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->prefix('VNĐ'),

            Textarea::make('note')
                ->label('Ghi chú')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Học viên')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('enrolled_at')
                    ->label('Ngày đăng ký')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'Đang học' => 'success',
                        'Hoàn thành' => 'info',
                        'Đã hủy' => 'danger',
                        'Tạm dừng' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('total_amount')
                    ->label('Học phí')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' đ')
                    ->alignment('right'),

                TextColumn::make('paid_amount')
                    ->label('Đã thanh toán')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' đ')
                    ->alignment('right')
                    ->color(fn($record) =>
                        $record->paid_amount >= $record->total_amount ? 'success' : 'warning'),

                TextColumn::make('remaining')
                    ->label('Còn lại')
                    ->getStateUsing(fn($record) => $record->total_amount - $record->paid_amount)
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' đ')
                    ->alignment('right')
                    ->color(fn($record) =>
                        ($record->total_amount - $record->paid_amount) > 0 ? 'danger' : 'success'),

                TextColumn::make('note')
                    ->label('Ghi chú')
                    ->placeholder('—')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Đang học' => 'Đang học',
                        'Hoàn thành' => 'Hoàn thành',
                        'Đã hủy' => 'Đã hủy',
                        'Tạm dừng' => 'Tạm dừng',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Thêm học viên')
                    ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])),
            ])
            ->recordActions([
                Action::make('pay')
                    ->label('Thu tiền')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn($record) => $record->paid_amount < $record->total_amount)
                    ->form([
                        TextInput::make('amount')
                            ->label('Số tiền thu')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->prefix('VNĐ')
                            ->default(fn($record) => $record->total_amount - $record->paid_amount),
                    ])
                    ->action(function ($record, array $data) {
                        $newPaid = $record->paid_amount + $data['amount'];
                        $record->update([
                            'paid_amount' => min($newPaid, $record->total_amount),
                            'status' => $newPaid >= $record->total_amount ? 'Đang học' : $record->status,
                        ]);
                        Notification::make()
                            ->success()
                            ->title('Đã ghi nhận thanh toán ' . number_format($data['amount'], 0, ',', '.') . ' đ')
                            ->send();
                    }),

                EditAction::make()
                    ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])),

                DeleteAction::make()
                    ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])),
            ])
            ->defaultSort('enrolled_at', 'desc')
            ->emptyStateHeading('Chưa có học viên nào')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
