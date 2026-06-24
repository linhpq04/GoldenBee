<?php

namespace App\Filament\Admin\Resources\Salaries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SalaryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin chung')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('Nhân viên')
                            ->relationship('user', 'name', fn($query) => $query->whereNull('left_at'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('salary_type')
                            ->label('Loại lương')
                            ->required()
                            ->options([
                                'Lương tháng cố định' => 'Lương tháng cố định',
                                'Lương theo giờ' => 'Lương theo giờ',
                                'Hoa hồng' => 'Hoa hồng',
                                'Thưởng' => 'Thưởng',
                                'Tạm ứng' => 'Tạm ứng',
                                'Khác' => 'Khác',
                            ])
                            ->default('Lương tháng cố định')
                            ->live()
                            ->afterStateUpdated(function (callable $set) {
                                $set('work_hours', 0);
                                $set('hourly_rate', 0);
                                $set('work_days', 0);
                                $set('bonus', 0);
                                $set('allowance', 0);
                                $set('deduction', 0);
                                $set('advance', 0);
                                $set('base_salary', 0);
                                $set('net_salary', 0);
                                $set('project_id', null);
                            }),

                        DatePicker::make('payment_date')
                            ->label('Ngày thanh toán')
                            ->required()
                            ->default(now())
                            ->displayFormat('d/m/Y'),

                        Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->options([
                                'Chờ duyệt' => 'Chờ duyệt',
                                'Đã duyệt' => 'Đã duyệt',
                                'Đã thanh toán' => 'Đã thanh toán',
                                'Từ chối' => 'Từ chối',
                            ])
                            ->default('Chờ duyệt'),

                        Select::make('project_id')
                            ->label('Dự án liên quan')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->nullable()
                            ->placeholder('Không gắn với dự án nào')
                            ->hidden(fn(callable $get) => in_array($get('salary_type'), [
                                'Lương tháng cố định',
                                'Tạm ứng',
                            ]) || $get('salary_type') === null),
                    ]),

                Section::make('Kỳ lương')
                    ->columns(3)
                    // Tạm ứng và Thưởng không cần kỳ lương
                    ->visible(fn(callable $get) => !in_array($get('salary_type'), ['Thưởng', 'Tạm ứng']))
                    ->schema([
                        TextInput::make('period')
                            ->label('Kỳ lương')
                            ->placeholder('VD: Tháng 12/2025, Q4/2025'),

                        DatePicker::make('from_date')
                            ->label('Từ ngày')
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('to_date')
                            ->label('Đến ngày')
                            ->displayFormat('d/m/Y')
                            ->afterOrEqual('from_date'),
                    ]),

                // Chỉ lương theo giờ mới cần section này
                Section::make('Chi tiết công việc')
                    ->columns(3)
                    ->visible(fn(callable $get) => $get('salary_type') === 'Lương theo giờ')
                    ->schema([
                        TextInput::make('work_hours')
                            ->label('Số giờ làm việc')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('giờ')
                            ->live(debounce: 500)
                            ->afterStateUpdated(
                                fn($state, callable $set, callable $get) =>
                                self::recalcNet($get, $set)
                            ),

                        TextInput::make('hourly_rate')
                            ->label('Đơn giá giờ')
                            ->numeric()
                            ->default(0)
                            ->prefix('đ')
                            ->live(debounce: 500)
                            ->formatStateUsing(fn($state) => number_format((float) str_replace('.', '', (string) $state), 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state))
                            ->afterStateUpdated(
                                fn($state, callable $set, callable $get) =>
                                self::recalcNet($get, $set)
                            ),

                        TextInput::make('work_days')
                            ->label('Số ngày làm việc')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('ngày'),
                    ]),

                Section::make('Chi tiết số tiền')
                    ->columns(3)
                    ->schema([
                        // Label thay đổi theo loại lương
                        TextInput::make('base_salary')
                            ->label(fn(callable $get) => match ($get('salary_type')) {
                                'Hoa hồng' => 'Tiền hoa hồng',
                                'Thưởng' => 'Số tiền thưởng',
                                'Tạm ứng' => 'Số tiền tạm ứng',
                                default => 'Số tiền cơ bản',
                            })
                            ->required()
                            ->default(0)
                            ->prefix('đ')
                            // Lương theo giờ: tự tính, không cho nhập
                            ->readOnly(fn(callable $get) => $get('salary_type') === 'Lương theo giờ')
                            ->live(debounce: 500)
                            ->formatStateUsing(fn($state) => number_format((float) str_replace('.', '', (string) $state), 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state))
                            ->afterStateUpdated(
                                fn($state, callable $set, callable $get) =>
                                self::recalcNet($get, $set)
                            ),

                        // Thưởng thêm (ẩn với Thưởng, Tạm ứng vì không có ý nghĩa)
                        TextInput::make('bonus')
                            ->label('Tiền thưởng thêm')
                            ->default(0)
                            ->prefix('đ')
                            ->visible(fn(callable $get) => in_array($get('salary_type'), [
                                'Lương tháng cố định',
                                'Lương theo giờ',
                                'Hoa hồng',
                                'Khác',
                            ]))
                            ->live(debounce: 500)
                            ->formatStateUsing(fn($state) => number_format((float) str_replace('.', '', (string) $state), 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state))
                            ->afterStateUpdated(
                                fn($state, callable $set, callable $get) =>
                                self::recalcNet($get, $set)
                            ),

                        // Phụ cấp (chỉ lương tháng, giờ, khác)
                        TextInput::make('allowance')
                            ->label('Phụ cấp')
                            ->default(0)
                            ->prefix('đ')
                            ->visible(fn(callable $get) => in_array($get('salary_type'), [
                                'Lương tháng cố định',
                                'Lương theo giờ',
                                'Khác',
                            ]))
                            ->live(debounce: 500)
                            ->formatStateUsing(fn($state) => number_format((float) str_replace('.', '', (string) $state), 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state))
                            ->afterStateUpdated(
                                fn($state, callable $set, callable $get) =>
                                self::recalcNet($get, $set)
                            ),

                        // Khấu trừ (ẩn với Tạm ứng vì bản thân nó đã là khoản trừ)
                        TextInput::make('deduction')
                            ->label('Khấu trừ')
                            ->helperText(fn(callable $get) => match ($get('salary_type')) {
                                'Hoa hồng' => 'Thuế TNCN trên hoa hồng...',
                                'Thưởng' => 'Thuế TNCN trên thưởng...',
                                default => 'Thuế TNCN, BHXH, BHYT...',
                            })
                            ->default(0)
                            ->prefix('đ')
                            ->visible(fn(callable $get) => $get('salary_type') !== 'Tạm ứng')
                            ->live(debounce: 500)
                            ->formatStateUsing(fn($state) => number_format((float) str_replace('.', '', (string) $state), 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state))
                            ->afterStateUpdated(
                                fn($state, callable $set, callable $get) =>
                                self::recalcNet($get, $set)
                            ),

                        // Tạm ứng đã nhận (ẩn khi chính nó là Tạm ứng)
                        TextInput::make('advance')
                            ->label('Tạm ứng đã nhận')
                            ->default(0)
                            ->prefix('đ')
                            ->visible(fn(callable $get) => $get('salary_type') !== 'Tạm ứng')
                            ->live(debounce: 500)
                            ->formatStateUsing(fn($state) => number_format((float) str_replace('.', '', (string) $state), 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state))
                            ->afterStateUpdated(
                                fn($state, callable $set, callable $get) =>
                                self::recalcNet($get, $set)
                            ),

                        Placeholder::make('formula')
                            ->label('Công thức')
                            ->content(fn(callable $get) => match ($get('salary_type')) {
                                'Lương tháng cố định' => 'Thực nhận = Cơ bản + Thưởng + Phụ cấp - Khấu trừ - Tạm ứng',
                                'Lương theo giờ' => 'Thực nhận = (Giờ × Đơn giá) + Thưởng + Phụ cấp - Khấu trừ - Tạm ứng',
                                'Hoa hồng' => 'Thực nhận = Hoa hồng + Thưởng thêm - Khấu trừ thuế',
                                'Thưởng' => 'Thực nhận = Số tiền thưởng - Khấu trừ thuế',
                                'Tạm ứng' => 'Thực nhận = Số tiền tạm ứng',
                                default => 'Thực nhận = Cơ bản + Thưởng + Phụ cấp - Khấu trừ - Tạm ứng',
                            })
                            ->visible(fn(callable $get) => $get('salary_type') !== null),

                        TextInput::make('net_salary')
                            ->label('Tổng thực nhận')
                            ->required()
                            ->readOnly()
                            ->default(0)
                            ->prefix('đ')
                            ->formatStateUsing(fn($state) => number_format((float) str_replace('.', '', (string) $state), 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state)),
                    ]),

                Section::make('Phương thức thanh toán')
                    ->columns(2)
                    ->schema([
                        Select::make('payment_method')
                            ->label('Hình thức thanh toán')
                            ->required()
                            ->options([
                                'Tiền mặt' => 'Tiền mặt',
                                'Chuyển khoản' => 'Chuyển khoản',
                                'Khác' => 'Khác',
                            ])
                            ->default('Chuyển khoản')
                            ->live(),

                        TextInput::make('transaction_code')
                            ->label(fn(callable $get) => match ($get('payment_method')) {
                                'Khác' => 'Mã/Ghi chú giao dịch',
                                default => 'Mã giao dịch ngân hàng',
                            })
                            ->placeholder(fn(callable $get) => match ($get('payment_method')) {
                                'Chuyển khoản' => 'VD: FT26174123456',
                                'Khác' => 'Ghi rõ hình thức và mã nếu có',
                                default => '',
                            })
                            ->visible(fn(callable $get) => $get('payment_method') !== 'Tiền mặt')
                            ->maxLength(255),
                    ]),

                Section::make('Thông tin bổ sung')
                    ->schema([
                        Textarea::make('detail_description')
                            ->label('Mô tả chi tiết')
                            ->rows(3),

                        Textarea::make('note')
                            ->label('Ghi chú')
                            ->rows(3),

                        Placeholder::make('approved_by_name')
                            ->label('Người duyệt')
                            ->content(fn($record) => $record?->approvedBy?->name ?? '—')
                            ->visible(fn($record) => $record && in_array($record->status, [
                                'Đã duyệt',
                                'Đã thanh toán',
                            ])),

                        FileUpload::make('file_path')
                            ->label('File đính kèm')
                            ->helperText('Phiếu lương, hợp đồng, biên bản...')
                            ->disk('public')
                            ->directory('salaries')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(10240),
                    ]),
            ]);
    }

    private static function recalcNet(callable $get, callable $set): void
    {
        $type = $get('salary_type');

        // Lương theo giờ: tự tính base_salary
        if ($type === 'Lương theo giờ') {
            $hours = (float) ($get('work_hours') ?? 0);
            $rate = (float) str_replace('.', '', (string) ($get('hourly_rate') ?? 0));
            $set('base_salary', number_format(max(0, round($hours * $rate)), 0, ',', '.'));
        }

        $base = (float) str_replace('.', '', (string) ($get('base_salary') ?? 0));
        $bonus = (float) str_replace('.', '', (string) ($get('bonus') ?? 0));
        $allowance = (float) str_replace('.', '', (string) ($get('allowance') ?? 0));
        $deduction = (float) str_replace('.', '', (string) ($get('deduction') ?? 0));
        $advance = (float) str_replace('.', '', (string) ($get('advance') ?? 0));

        $net = match ($type) {
            'Tạm ứng' => $base,
            'Thưởng' => $base - $deduction,
            'Hoa hồng' => $base + $bonus - $deduction,
            default => $base + $bonus + $allowance - $deduction - $advance,
        };

        $set('net_salary', number_format(max(0, round($net)), 0, ',', '.'));
    }
}