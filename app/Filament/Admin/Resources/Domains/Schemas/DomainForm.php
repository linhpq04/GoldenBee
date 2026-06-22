<?php

namespace App\Filament\Admin\Resources\Domains\Schemas;

use App\Models\Hosting;
use App\Models\Project;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DomainForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([

                // ── CỘT TRÁI (span 2) ─────────────────────────────────
                Section::make('Thông tin tên miền')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('domain_name')
                            ->label('Tên miền')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('example.com')
                            ->helperText('Chỉ nhập tên miền, không có http/https')
                            ->columnSpanFull(),

                        Select::make('customer_id')
                            ->label('Khách hàng')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (callable $set) {
                                $set('project_id', null);
                                $set('hosting_id', null);
                            })
                            ->columnSpanFull(),

                        Select::make('project_id')
                            ->label('Dự án liên quan')
                            ->options(fn(callable $get) => Project::where('customer_id', $get('customer_id'))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Chọn dự án (nếu có)')
                            ->columnSpan(1),

                        Select::make('hosting_id')
                            ->label('Hosting liên kết')
                            ->options(fn(callable $get) => Hosting::where('customer_id', $get('customer_id'))
                                ->pluck('hosting_name', 'id'))
                            ->searchable()
                            ->placeholder('Chọn hosting (nếu có)')
                            ->helperText('Hosting đang trỏ tên miền này')
                            ->columnSpan(1),

                        TextInput::make('provider_name')
                            ->label('Nhà cung cấp')
                            ->placeholder('VD: GoDaddy, Namecheap, Mắt Bão, PA Việt Nam...')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                // ── CỘT PHẢI (span 1): Trạng thái & Cài đặt ──────────
                Section::make('Trạng thái')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->options([
                                'Hoạt động' => 'Hoạt động',
                                'Ngừng hoạt động' => 'Ngừng hoạt động',
                                'Hết hạn' => 'Hết hạn',
                                'Đang chuyển' => 'Đang chuyển',
                            ])
                            ->default('Hoạt động'),

                        Toggle::make('auto_renew')
                            ->label('Tự động gia hạn')
                            ->helperText('Bật nếu tên miền có gia hạn tự động')
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
                    ]),

                // ── FULL WIDTH: Thời hạn & Giá ────────────────────────
                Section::make('Thời hạn & Giá')
                    ->columnSpan(3)
                    ->columns(3)
                    ->schema([
                        DatePicker::make('registered_at')
                            ->label('Ngày đăng ký')
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        DatePicker::make('expires_at')
                            ->label('Ngày hết hạn')
                            ->displayFormat('d/m/Y')
                            ->afterOrEqual('registered_at')
                            ->live()
                            ->helperText(fn($state) => $state && \Carbon\Carbon::parse($state)->isPast()
                                ? '⚠️ Tên miền đã hết hạn'
                                : null)
                            ->columnSpan(1),

                        DatePicker::make('last_renewed_at')
                            ->label('Gia hạn lần cuối')
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        TextInput::make('purchase_price')
                            ->label('Giá mua vào')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->prefix('VNĐ')
                            ->columnSpan(1),

                        TextInput::make('service_fee')
                            ->label('Phí dịch vụ')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->prefix('VNĐ')
                            ->columnSpan(1),

                        TextInput::make('selling_price')
                            ->label('Giá bán')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->prefix('VNĐ')
                            ->columnSpan(1),
                    ]),

                // ── FULL WIDTH: Nameservers ────────────────────────────
                Section::make('Thông tin kỹ thuật')
                    ->columnSpan(3)
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('servername_1')
                            ->label('Nameserver 1')
                            ->placeholder('ns1.example.com')
                            ->columnSpan(1),

                        TextInput::make('servername_2')
                            ->label('Nameserver 2')
                            ->placeholder('ns2.example.com')
                            ->columnSpan(1),

                        TextInput::make('servername_3')
                            ->label('Nameserver 3')
                            ->placeholder('ns3.example.com')
                            ->columnSpan(1),

                        TextInput::make('servername_4')
                            ->label('Nameserver 4')
                            ->placeholder('ns4.example.com')
                            ->columnSpan(1),
                    ]),

                // ── FULL WIDTH: Đăng nhập Registrar ───────────────────
                Section::make('Thông tin đăng nhập Registrar')
                    ->columnSpan(3)
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('login_username')
                            ->label('Tên đăng nhập')
                            ->maxLength(50)
                            ->columnSpan(1),

                        TextInput::make('login_password')
                            ->label('Mật khẩu')
                            ->password()
                            ->revealable()
                            ->helperText('Mật khẩu sẽ được mã hóa khi lưu')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Textarea::make('note')
                            ->label('Ghi chú')
                            ->placeholder('Thông tin bổ sung...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
