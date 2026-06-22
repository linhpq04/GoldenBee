<?php

namespace App\Filament\Admin\Resources\Hostings\Schemas;

use App\Models\Project;
use App\Models\Server;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HostingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                // ── CỘT TRÁI (span 2): Thông tin hosting ─────────────
                Section::make('Thông tin hosting')
                    ->columnSpan(2)
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
                            ->options(fn(callable $get) => Project::where('customer_id', $get('customer_id'))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Chọn dự án (nếu có)')
                            ->columnSpan(1),

                        Select::make('server_id')
                            ->label('Máy chủ vật lý')
                            ->options(fn() => Server::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Chọn máy chủ (nếu có)')
                            ->columnSpan(1),

                        Select::make('provider_name')
                            ->label('Nhà cung cấp')
                            ->options([
                                'Vietnix' => 'Vietnix',
                                'AZDIGI' => 'AZDIGI',
                                'Mắt Bão' => 'Mắt Bão',
                                'PA Việt Nam' => 'PA Việt Nam',
                                'DigitalOcean' => 'DigitalOcean',
                                'Vultr' => 'Vultr',
                                'Linode' => 'Linode',
                                'AWS' => 'AWS',
                                'Khác' => 'Khác',
                            ])
                            ->searchable()
                            ->placeholder('Chọn nhà cung cấp')
                            ->columnSpan(1),

                        TextInput::make('hosting_name')
                            ->label('Tên hosting')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('VD: Dung lượng: 10GB - Băng thông: Không giới hạn')
                            ->columnSpanFull(),

                        Select::make('hosting_type')
                            ->label('Loại hosting')
                            ->required()
                            ->options([
                                'VPS' => 'VPS',
                                'Shared' => 'Shared Hosting',
                                'Dedicated' => 'Dedicated Server',
                                'Cloud' => 'Cloud Hosting',
                                'Khác' => 'Khác',
                            ])
                            ->default('Shared')
                            ->columnSpan(1),

                        Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->options([
                                'Hoạt động' => 'Hoạt động',
                                'Ngừng hoạt động' => 'Ngừng hoạt động',
                                'Hết hạn' => 'Hết hạn',
                                'Tạm dừng' => 'Tạm dừng',
                            ])
                            ->default('Hoạt động')
                            ->columnSpan(1),

                        TextInput::make('primary_name')
                            ->label('Tên miền chính')
                            ->placeholder('VD: example.com')
                            ->maxLength(50)
                            ->columnSpanFull(),
                    ]),

                // ── CỘT PHẢI (span 1): Cài đặt gia hạn ──────────────
                Section::make('Cài đặt gia hạn')
                    ->columnSpan(1)
                    ->schema([
                        Toggle::make('auto_renew')
                            ->label('Tự động gia hạn')
                            ->default(false)
                            ->inline(false),

                        TextInput::make('remind_days')
                            ->label('Nhắc trước')
                            ->numeric()
                            ->default(30)
                            ->minValue(1)
                            ->maxValue(365)
                            ->suffix('ngày')
                            ->helperText('Số ngày trước khi hết hạn sẽ nhận thông báo'),

                        DatePicker::make('setup_at')
                            ->label('Ngày thiết lập')
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('expires_at')
                            ->label('Ngày gia hạn')
                            ->displayFormat('d/m/Y')
                            ->live()
                            ->helperText(fn($state) => $state && \Carbon\Carbon::parse($state)->isPast()
                                ? '⚠️ Hosting đã hết hạn'
                                : null),
                    ]),

                // ── FULL WIDTH: Giá & Chu kỳ ─────────────────────────
                Section::make('Giá & Chu kỳ thanh toán')
                    ->columnSpan(3)
                    ->columns(4)
                    ->schema([
                        TextInput::make('purchase_price')
                            ->label('Giá mua vào')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->prefix('VNĐ')
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $fee = $get('service_fee') ?? 0;
                                $set('selling_price', (float) $state + (float) $fee);
                            })
                            ->columnSpan(1),

                        TextInput::make('service_fee')
                            ->label('Phí dịch vụ')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->prefix('VNĐ')
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $purchase = $get('purchase_price') ?? 0;
                                $set('selling_price', (float) $purchase + (float) $state);
                            })
                            ->columnSpan(1),

                        TextInput::make('selling_price')
                            ->label('Giá bán')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->prefix('VNĐ')
                            ->helperText('Tự động tính = Giá mua + Phí dịch vụ')
                            ->columnSpan(1),

                        Select::make('billing_cycle')
                            ->label('Chu kỳ thanh toán')
                            ->required()
                            ->options([
                                'Hàng tháng' => 'Hàng tháng',
                                'Nửa năm' => 'Nửa năm',
                                'Hàng năm' => 'Hàng năm',
                                'Khác' => 'Khác',
                            ])
                            ->default('Hàng năm')
                            ->columnSpan(1),
                    ]),

                // ── FULL WIDTH: Thông số kỹ thuật ────────────────────
                Section::make('Thông số kỹ thuật')
                    ->columnSpan(3)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('server_config')
                            ->label('Cấu hình')
                            ->placeholder("RAM: 4GB\nCPU: 2 Core\nDisk: 50GB SSD\nBandwidth: Unlimited")
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                // ── FULL WIDTH: Thông tin đăng nhập ──────────────────
                Section::make('Thông tin đăng nhập')
                    ->columnSpan(3)
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('cpanel_username')
                            ->label('cPanel Username')
                            ->maxLength(50),

                        TextInput::make('cpanel_password')
                            ->label('cPanel Password')
                            ->password()
                            ->revealable()
                            ->helperText('Mật khẩu sẽ được mã hóa'),

                        TextInput::make('ftp_host')
                            ->label('FTP Host')
                            ->maxLength(50),

                        TextInput::make('ftp_username')
                            ->label('FTP Username')
                            ->maxLength(50),

                        TextInput::make('ftp_password')
                            ->label('FTP Password')
                            ->password()
                            ->revealable(),

                        TextInput::make('db_host')
                            ->label('Database Host')
                            ->maxLength(50),

                        TextInput::make('db_name')
                            ->label('Database Name')
                            ->maxLength(50),

                        TextInput::make('db_username')
                            ->label('Database Username')
                            ->maxLength(50),

                        TextInput::make('db_password')
                            ->label('Database Password')
                            ->password()
                            ->revealable(),

                        Textarea::make('note')
                            ->label('Ghi chú')
                            ->placeholder('Thông tin bổ sung...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
