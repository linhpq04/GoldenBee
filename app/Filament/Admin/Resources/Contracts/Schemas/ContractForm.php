<?php

namespace App\Filament\Admin\Resources\Contracts\Schemas;

use App\Models\Project;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)
            ->components([
                // ── CỘT TRÁI: Thông tin hợp đồng ─────────────────────────
                Section::make('Thông tin hợp đồng')
                    ->columnSpan(1)
                    ->columns(2)
                    ->schema([

                        Select::make('customer_id')
                            ->label('Khách hàng')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('project_id', null))
                            ->columnSpan(1),

                        Select::make('project_id')
                            ->label('Dự án')
                            ->options(function (callable $get) {
                                $customerId = $get('customer_id');
                                if (!$customerId)
                                    return [];
                                return Project::where('customer_id', $customerId)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->placeholder('Chọn một tùy chọn')
                            ->columnSpan(1),

                        Select::make('type')
                            ->label('Loại hợp đồng')
                            ->required()
                            ->options([
                                'Hợp đồng dự án' => 'Hợp đồng dự án',
                                'Hợp đồng dịch vụ' => 'Hợp đồng dịch vụ',
                                'Hợp đồng bảo trì' => 'Hợp đồng bảo trì',
                                'Hợp đồng tư vấn' => 'Hợp đồng tư vấn',
                                'Hợp đồng thuê' => 'Hợp đồng thuê',
                                'Khác' => 'Khác',
                            ])
                            ->default('Hợp đồng dự án')
                            ->columnSpan(1),

                        Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->options([
                                'Bản nháp' => 'Bản nháp',
                                'Chờ ký' => 'Chờ ký',
                                'Đang hiệu lực' => 'Đang hiệu lực',
                                'Hết hạn' => 'Hết hạn',
                                'Đã hủy' => 'Đã hủy',
                            ])
                            ->default('Bản nháp')
                            ->columnSpan(1),

                        Select::make('parent_id')
                            ->label('Hợp đồng cha')
                            ->relationship(
                                'parent',
                                'title',
                                fn(callable $get, $record) => $record
                                ? \App\Models\Contract::where('id', '!=', $record->id)
                                : \App\Models\Contract::query()
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('Không có (hợp đồng gốc)')
                            ->helperText('Chọn nếu đây là phụ lục/gia hạn của hợp đồng khác')
                            ->columnSpanFull(),

                        TextInput::make('title')
                            ->label('Tiêu đề')
                            ->placeholder('Tiêu đề hợp đồng')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Mô tả')
                            ->placeholder('Mô tả chi tiết...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                // ── CỘT PHẢI: Giá trị hợp đồng ───────────────────────────
                Section::make('Giá trị hợp đồng')
                    ->columnSpan(1)
                    ->columns(2)
                    ->schema([

                        TextInput::make('contract_value')
                            ->label('Giá trị hợp đồng')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->prefix('VNĐ')
                            ->live(debounce: 500)
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::recalcTotal($state, $get('has_vat'), $get('vat_percent'), $set);
                            }),

                        Toggle::make('has_vat')
                            ->label('Tính VAT')
                            ->required()
                            ->default(false)
                            ->live()
                            ->columnSpan(1)
                            ->inline(false)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (!$state) {
                                    $set('vat_percent', 0);
                                    $set('total', $get('contract_value') ?? 0);
                                } else {
                                    self::recalcTotal($get('contract_value'), $state, $get('vat_percent'), $set);
                                }
                            }),

                        TextInput::make('vat_percent')
                            ->label('% VAT')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->live(debounce: 500)
                            ->columnSpan(1)
                            ->disabled(fn(callable $get) => !$get('has_vat'))
                            ->dehydrated(fn(callable $get) => (bool) $get('has_vat'))
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::recalcTotal($get('contract_value'), $get('has_vat'), $state, $set);
                            }),

                        TextInput::make('total')
                            ->label('Tổng tiền')
                            ->prefix('VNĐ')
                            ->readOnly()
                            ->default(0)
                            ->columnSpanFull()
                            ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state)),
                    ]),

                // ── THỜI HẠN & TÀI LIỆU ──────────────────────────────────
                Section::make('Thời hạn & Tài liệu')
                    ->columnSpan(1)
                    ->columns(2)
                    ->schema([

                        DatePicker::make('signed_at')
                            ->label('Ngày ký')
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        DatePicker::make('start_date')
                            ->label('Ngày bắt đầu')
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        DatePicker::make('end_date')
                            ->label('Ngày kết thúc')
                            ->displayFormat('d/m/Y')
                            ->afterOrEqual('start_date')
                            ->columnSpan(1),

                        FileUpload::make('file_path')
                            ->label('File hợp đồng')
                            ->disk('public')
                            ->directory('contracts')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'image/*',
                            ])
                            ->maxSize(10240)
                            ->columnSpan(1),
                    ]),

                // ── GHI CHÚ ───────────────────────────────────────────────
                Section::make('Ghi chú')
                    ->columnSpan(1)
                    ->collapsible()
                    ->schema([

                        Textarea::make('note')
                            ->label('Ghi chú')
                            ->placeholder('Ghi chú thêm...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function recalcTotal($contractValue, $hasVat, $vatPercent, callable $set): void
    {
        $value = (float) ($contractValue ?? 0);
        $vat = $hasVat ? (float) ($vatPercent ?? 0) : 0;

        $total = $value * (1 + $vat / 100);

        $set('total', number_format(round($total), 0, ',', '.'));
    }
}
