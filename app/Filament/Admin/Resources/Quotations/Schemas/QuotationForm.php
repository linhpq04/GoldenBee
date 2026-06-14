<?php

namespace App\Filament\Admin\Resources\Quotations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuotationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Thông tin báo giá')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('customer_id')
                            ->label('Khách hàng')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->options([
                                'Bản nháp' => 'Bản nháp',
                                'Chờ duyệt' => 'Chờ duyệt',
                                'Đã gửi' => 'Đã gửi',
                                'Chấp nhận' => 'Chấp nhận',
                                'Từ chối' => 'Từ chối',
                                'Đã chuyển DA' => 'Đã chuyển DA',
                            ])
                            ->default('Bản nháp'),

                        DatePicker::make('valid_until')
                            ->label('Hiệu lực đến')
                            ->default(now()->addDays(30)),

                        TextInput::make('title')
                            ->label('Tiêu đề')
                            ->placeholder('Tiêu đề báo giá')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Mô tả')
                            ->placeholder('Mô tả chi tiết về báo giá...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Chi tiết sản phẩm/dịch vụ')
                    ->columnSpan(1)
                    ->schema([
                        Repeater::make('items')
                            ->label('Items')
                            ->relationship('items')
                            ->schema([
                                Select::make('service_type')
                                    ->label('Loại dịch vụ')
                                    ->required()
                                    ->options([
                                        'Website' => 'Website',
                                        'Phần mềm' => 'Phần mềm',
                                        'App Mobile' => 'App Mobile',
                                        'Bảo trì' => 'Bảo trì',
                                        'Tên miền' => 'Tên miền',
                                        'Hosting' => 'Hosting',
                                        'Khác' => 'Khác',
                                    ]),

                                TextInput::make('name')
                                    ->label('Tên sản phẩm/dịch vụ')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('quantity')
                                    ->label('Số lượng')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(
                                        fn($state, callable $set, callable $get) =>
                                        $set('total', self::calcTotal($get))
                                    ),

                                TextInput::make('unit')
                                    ->label('Đơn vị')
                                    ->required()
                                    ->default('Gói')
                                    ->datalist(['Gói', 'Tháng', 'Năm', 'Giờ', 'Cái']),

                                TextInput::make('unit_price')
                                    ->label('Đơn giá')
                                    ->required()
                                    ->default(0)
                                    ->minValue(0)
                                    ->prefix('VNĐ')
                                    ->live()
                                    ->columnSpanFull()
                                    ->formatStateUsing(fn($state) => number_format((float) str_replace('.', '', (string) $state), 0, ',', '.'))
                                    ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state))
                                    ->afterStateUpdated(
                                        fn($state, callable $set, callable $get) =>
                                        $set('total', self::calcTotal($get))
                                    ),

                                TextInput::make('discount_percent')
                                    ->label('Giảm giá (%)')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->live()
                                    ->afterStateUpdated(
                                        fn($state, callable $set, callable $get) =>
                                        $set('total', self::calcTotal($get))
                                    ),

                                TextInput::make('tax_percent')
                                    ->label('Thuế (%)')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->suffix('%')
                                    ->live()
                                    ->afterStateUpdated(
                                        fn($state, callable $set, callable $get) =>
                                        $set('total', self::calcTotal($get))
                                    ),

                                TextInput::make('total')
                                    ->label('Thành tiền')
                                    ->prefix('VNĐ')
                                    ->readOnly()
                                    ->default(0)
                                    ->columnSpanFull()
                                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.'))
                                    ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state)),

                                Textarea::make('description')
                                    ->label('Mô tả')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Thêm dịch vụ')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                self::updateSummary($state, $set);
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Tổng kết')
                    ->columnSpan(1)
                    ->columns(3)
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Tạm tính')
                            ->prefix('VNĐ')
                            ->readOnly()
                            ->default(0)
                            ->columnSpan(1)
                            ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state)),

                        TextInput::make('tax_amount')
                            ->label('Tổng thuế')
                            ->prefix('VNĐ')
                            ->readOnly()
                            ->default(0)
                            ->columnSpan(1)
                            ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state)),

                        TextInput::make('total')
                            ->label('Tổng tiền')
                            ->prefix('VNĐ')
                            ->readOnly()
                            ->default(0)
                            ->columnSpan(1)
                            ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state)),
                    ]),

                Section::make('Ghi chú')
                    ->columnSpan(1)
                    ->schema([
                        Textarea::make('note')
                            ->label('Ghi chú')
                            ->placeholder('Điều khoản, điều kiện thanh toán, thời gian thực hiện...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function calcTotal(callable $get): string
    {
        $qty = (float) ($get('quantity') ?? 0);
        $price = (float) str_replace('.', '', (string) ($get('unit_price') ?? 0));
        $discount = (float) ($get('discount_percent') ?? 0);
        $tax = (float) ($get('tax_percent') ?? 0);

        $afterDiscount = $price * $qty * (1 - $discount / 100);
        return number_format(round($afterDiscount * (1 + $tax / 100)), 0, ',', '.');
    }

    private static function updateSummary(array $items, callable $set): void
    {
        $subtotal = 0;
        $taxAmount = 0;

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $price = (float) str_replace('.', '', (string) ($item['unit_price'] ?? 0));
            $discount = (float) ($item['discount_percent'] ?? 0);
            $tax = (float) ($item['tax_percent'] ?? 0);

            $afterDiscount = $price * $qty * (1 - $discount / 100);
            $itemTax = $afterDiscount * ($tax / 100);

            $subtotal += $afterDiscount;
            $taxAmount += $itemTax;
        }

        $set('subtotal', number_format(round($subtotal), 0, ',', '.'));
        $set('tax_amount', number_format(round($taxAmount), 0, ',', '.'));
        $set('total', number_format(round($subtotal + $taxAmount), 0, ',', '.'));
    }
}