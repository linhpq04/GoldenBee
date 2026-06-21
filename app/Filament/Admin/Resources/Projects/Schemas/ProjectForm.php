<?php

namespace App\Filament\Admin\Resources\Projects\Schemas;

use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([

                // ── CỘT TRÁI (span 2): Thông tin dự án ───────────────
                Section::make('Thông tin dự án')
                    ->description('Thông tin cơ bản về dự án và khách hàng')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        Select::make('customer_id')
                            ->label('Khách hàng')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        Select::make('quotation_id')
                            ->label('Báo giá liên quan')
                            ->relationship('quotation', 'title')
                            ->searchable()
                            ->preload()
                            ->placeholder('Chọn một tùy chọn')
                            ->helperText('Chọn báo giá nếu dự án được tạo từ báo giá')
                            ->columnSpan(1),

                        TextInput::make('name')
                            ->label('Tên dự án')
                            ->placeholder('VD: Website bán hàng ABC, Phần mềm quản lý XYZ')
                            ->required()
                            ->maxLength(50)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Mô tả dự án')
                            ->placeholder('Mô tả chi tiết về yêu cầu, phạm vi công việc...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                // ── CỘT PHẢI (span 1): Phân loại ─────────────────────
                Section::make('Phân loại')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('type')
                            ->label('Loại dự án')
                            ->required()
                            ->options([
                                'Website' => 'Website',
                                'SEO' => 'SEO',
                                'Mobile App' => 'Mobile App',
                                'Phần mềm' => 'Phần mềm',
                                'Khác' => 'Khác',
                            ])
                            ->default('Website'),

                        Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->options([
                                'Lên kế hoạch' => 'Lên kế hoạch',
                                'Đang thực hiện' => 'Đang thực hiện',
                                'Tạm dừng' => 'Tạm dừng',
                                'Hoàn thành' => 'Hoàn thành',
                                'Đã hủy' => 'Đã hủy',
                            ])
                            ->default('Lên kế hoạch'),

                        Select::make('priority')
                            ->label('Ưu tiên')
                            ->required()
                            ->options([
                                'Thấp' => 'Thấp',
                                'Trung bình' => 'Trung bình',
                                'Cao' => 'Cao',
                                'Khẩn cấp' => 'Khẩn cấp',
                            ])
                            ->default('Trung bình'),

                        Select::make('manager_id')
                            ->label('Người quản lý')
                            ->options(fn() => User::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Chưa phân công'),
                    ]),

                // ── FULL WIDTH: Thời gian & Tài chính ─────────────────
                Section::make('Thời gian & Tài chính')
                    ->description('Thời gian thực hiện và giá trị dự án')
                    ->columnSpan(3)
                    ->columns(4)
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Ngày bắt đầu')
                            ->displayFormat('d/m/Y')
                            ->default(today())
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Tự tính lại warranty_expires_at nếu có warranty_months
                                $months = $get('warranty_months');
                                if ($state && $months && !$get('warranty_lifetime')) {
                                    $set(
                                        'warranty_expires_at',
                                        Carbon::parse($state)->addMonths((int) $months)->format('Y-m-d')
                                    );
                                }
                            })
                            ->columnSpan(1),

                        DatePicker::make('end_date')
                            ->label('Ngày kết thúc')
                            ->displayFormat('d/m/Y')
                            ->afterOrEqual('start_date')
                            ->live()
                            ->helperText(fn($state) => $state && Carbon::parse($state)->isPast()
                                ? '⚠️ Ngày này đã qua'
                                : null)
                            ->columnSpan(1),

                        TextInput::make('contract_value')
                            ->label('Giá trị hợp đồng')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->prefix('VNĐ')
                            ->helperText('Giá trị thanh toán cho khách hàng')
                            ->columnSpan(1),

                        TextInput::make('budget')
                            ->label('Ngân sách chi phí')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('VNĐ')
                            ->helperText('Ngân sách dự kiến cho chi phí')
                            ->columnSpan(1),
                    ]),

                // ── FULL WIDTH: Bảo hành ───────────────────────────────
                Section::make('Chính sách bảo hành')
                    ->columnSpan(3)
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        Toggle::make('warranty_lifetime')
                            ->label('Bảo hành trọn đời')
                            ->helperText('Bật nếu dự án có bảo hành trọn đời')
                            ->default(false)
                            ->live()
                            ->inline(false)
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('warranty_months', null);
                                    $set('warranty_expires_at', null);
                                }
                            })
                            ->columnSpan(1),

                        TextInput::make('warranty_months')
                            ->label('Thời gian bảo hành (tháng)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('tháng')
                            ->helperText('Số tháng bảo hành (nếu không trọn đời)')
                            ->disabled(fn(callable $get) => (bool) $get('warranty_lifetime'))
                            ->dehydrated(fn(callable $get) => !(bool) $get('warranty_lifetime'))
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state && !$get('warranty_lifetime')) {
                                    $start = $get('start_date') ?? now()->format('Y-m-d');
                                    $set(
                                        'warranty_expires_at',
                                        Carbon::parse($start)->addMonths((int) $state)->format('Y-m-d')
                                    );
                                }
                            })
                            ->columnSpan(1),

                        DatePicker::make('warranty_expires_at')
                            ->label('Ngày hết bảo hành')
                            ->displayFormat('d/m/Y')
                            ->helperText('Tự động tính nếu có thời gian bảo hành')
                            ->disabled(fn(callable $get) => (bool) $get('warranty_lifetime'))
                            ->dehydrated(fn(callable $get) => !(bool) $get('warranty_lifetime'))
                            ->columnSpan(1),
                    ]),

                // ── FULL WIDTH: Ghi chú ────────────────────────────────
                Section::make('Ghi chú thêm')
                    ->columnSpan(3)
                    ->collapsible()
                    ->schema([
                        Textarea::make('note')
                            ->label('Ghi chú')
                            ->placeholder('Thông tin bổ sung, yêu cầu đặc biệt...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);

    }
}
