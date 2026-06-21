<?php

namespace App\Filament\Admin\Resources\Invoices\Schemas;

use App\Models\Contract;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                Section::make('Thông tin chính')
                    ->columnSpan(1)
                    ->columns(2)
                    ->schema([
                        Select::make('invoice_type')
                            ->label('Loại hóa đơn')
                            ->required()
                            ->options([
                                'Chi phí' => '🛍️ Hóa đơn đầu vào (Chi phí)',
                                'Doanh thu' => '🏷️ Hóa đơn đầu ra (Doanh thu)',
                            ])
                            ->default('Doanh thu')
                            ->live()
                            ->columnSpanFull(),

                        // Chi phí: chọn nhà cung cấp
                        TextInput::make('provider_name')
                            ->label('Nhà cung cấp')
                            ->placeholder('Tên nhà cung cấp dịch vụ/sản phẩm')
                            ->maxLength(50)
                            ->visible(fn(callable $get) => $get('invoice_type') === 'Chi phí')
                            ->columnSpanFull(),

                        // Doanh thu: chọn khách hàng + email
                        Select::make('customer_id')
                            ->label('Khách hàng')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(fn(callable $get) => $get('invoice_type') === 'Doanh thu')
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('contract_id', null))
                            ->visible(fn(callable $get) => $get('invoice_type') === 'Doanh thu')
                            ->columnSpanFull(),

                        TextInput::make('invoice_email')
                            ->label('Email nhận hóa đơn')
                            ->email()
                            ->placeholder('email@example.com')
                            ->helperText('Email để gửi hóa đơn điện tử')
                            ->maxLength(50)
                            ->visible(fn(callable $get) => $get('invoice_type') === 'Doanh thu')
                            ->columnSpanFull(),

                        DatePicker::make('invoice_date')
                            ->label('Ngày hóa đơn')
                            ->required()
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        DatePicker::make('due_date')
                            ->label('Hạn thanh toán')
                            ->displayFormat('d/m/Y')
                            ->afterOrEqual('invoice_date')
                            ->columnSpan(1),
                    ]),

                Section::make('Trạng thái & Mã')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('code')
                            ->label('Mã hóa đơn')
                            ->placeholder('Tự động tạo')
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->options([
                                'Bản nháp' => 'Bản nháp',
                                'Đã gửi' => 'Đã gửi',
                                'Đã thanh toán' => 'Đã thanh toán',
                                'Quá hạn' => 'Quá hạn',
                                'Đã hủy' => 'Đã hủy',
                            ])
                            ->default('Bản nháp'),
                    ]),

                Section::make('Số tiền')
                    ->columnSpan(1)
                    ->columns(2)
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Tổng tiền trước thuế')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->prefix('VNĐ')
                            ->live(debounce: 500)
                            ->columnSpan(1)
                            ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state))
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::recalcTotal($state, $get('vat_percent'), $set);
                            }),

                        TextInput::make('vat_percent')
                            ->label('Thuế VAT (%)')
                            ->numeric()
                            ->default(10)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->live(debounce: 500)
                            ->columnSpan(1)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::recalcTotal($get('subtotal'), $state, $set);
                            }),

                        TextInput::make('vat_amount')
                            ->label('Tiền thuế')
                            ->readOnly()
                            ->default(0)
                            ->prefix('VNĐ')
                            ->columnSpan(1)
                            ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state)),

                        TextInput::make('total')
                            ->label('Tổng cộng')
                            ->readOnly()
                            ->default(0)
                            ->prefix('VNĐ')
                            ->columnSpan(1)
                            ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state)),
                    ]),

                Section::make('Thanh toán')
                    ->columnSpan(1)
                    ->columns(2)
                    ->schema([
                        TextInput::make('paid_amount')
                            ->label('Đã thanh toán')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->prefix('VNĐ')
                            ->columnSpan(1)
                            ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', (string) $state)),

                        DatePicker::make('paid_date')
                            ->label('Ngày thanh toán')
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        Select::make('payment_method')
                            ->label('Phương thức')
                            ->options([
                                'Tiền mặt' => 'Tiền mặt',
                                'Chuyển khoản' => 'Chuyển khoản',
                                'Khác' => 'Khác',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Liên kết & Ghi chú')
                    ->columnSpan(2)
                    ->collapsible()
                    ->schema([
                        Select::make('service_type')
                            ->label('Loại dịch vụ')
                            ->required()
                            ->options([
                                'Website' => 'Website',
                                'Hosting' => 'Hosting',
                                'Tên miền' => 'Tên miền',
                                'Bảo trì' => 'Bảo trì',
                                'Phần mềm' => 'Phần mềm',
                                'Tư vấn' => 'Tư vấn',
                                'Khác' => 'Khác',
                            ])
                            ->columnSpanFull(),

                        Select::make('contract_id')
                            ->label('Hợp đồng liên kết')
                            ->options(function (callable $get) {
                                $customerId = $get('customer_id');
                                if (!$customerId)
                                    return [];
                                return Contract::where('customer_id', $customerId)
                                    ->pluck('title', 'id');
                            })
                            ->searchable()
                            ->placeholder('Không liên kết hợp đồng')
                            ->visible(fn(callable $get) => $get('invoice_type') === 'Doanh thu')
                            ->columnSpanFull(),

                        FileUpload::make('file_path')
                            ->label('File hóa đơn')
                            ->disk('public')
                            ->directory('invoices')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/*',
                            ])
                            ->maxSize(10240)
                            ->columnSpanFull(),

                        Textarea::make('note')
                            ->label('Ghi chú nội bộ')
                            ->helperText('Ghi chú sử dụng nội bộ, không hiển thị trên hóa đơn')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('invoice_content')
                            ->label('Nội dung hóa đơn')
                            ->placeholder('VD: Thanh toán dịch vụ thiết kế website theo hợp đồng số...')
                            ->helperText('Nội dung mô tả chi tiết hiển thị trên hóa đơn gửi khách hàng')
                            ->rows(3)
                            ->visible(fn(callable $get) => $get('invoice_type') === 'Doanh thu')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function recalcTotal($subtotal, $vatPercent, callable $set): void
    {
        $sub = (float) str_replace('.', '', (string) ($subtotal ?? 0));
        $vat = (float) ($vatPercent ?? 0);

        $vatAmount = $sub * ($vat / 100);
        $total = $sub + $vatAmount;

        $set('vat_amount', number_format(round($vatAmount), 0, ',', '.'));
        $set('total', number_format(round($total), 0, ',', '.'));
    }
}
