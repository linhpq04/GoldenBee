<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Thông tin chung')
                            ->icon('heroicon-o-user')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Họ và tên')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                TextInput::make('password')
                                    ->label('Mật khẩu')
                                    ->password()
                                    ->revealable()
                                    ->minLength(8)
                                    ->required(fn(string $operation) => $operation === 'create')
                                    ->dehydrated(fn($state) => filled($state))
                                    ->dehydrateStateUsing(fn($state) => Hash::make($state)),

                                TextInput::make('phone')
                                    ->label('Số điện thoại')
                                    ->tel()
                                    ->prefixIcon('heroicon-o-phone'),

                                TextInput::make('position')
                                    ->label('Chức vụ'),

                                Select::make('status')
                                    ->label('Trạng thái')
                                    ->required()
                                    ->options([
                                        'Hoạt động' => 'Hoạt động',
                                        'Không hoạt động' => 'Không hoạt động',
                                        'Đình chỉ' => 'Đình chỉ',
                                    ])
                                    ->default('Hoạt động'),

                                Select::make('roles')
                                    ->label('Vai trò')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->helperText('Chọn vai trò cho người dùng')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tab::make('Thông tin cá nhân')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Section::make('Giấy tờ tùy thân')
                                    ->schema([
                                        TextInput::make('id_number')
                                            ->label('CCCD/CMND'),

                                        DatePicker::make('id_issued_date')
                                            ->label('Ngày cấp'),

                                        TextInput::make('id_issued_place')
                                            ->label('Nơi cấp')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),

                                Section::make('Thông tin khác')
                                    ->schema([
                                        DatePicker::make('date_of_birth')
                                            ->label('Ngày sinh'),

                                        Select::make('gender')
                                            ->label('Giới tính')
                                            ->options([
                                                'Nam' => 'Nam',
                                                'Nữ' => 'Nữ',
                                                'Khác' => 'Khác',
                                            ]),

                                        Textarea::make('address')
                                            ->label('Địa chỉ')
                                            ->columnSpanFull(),

                                        Textarea::make('emergency_contact')
                                            ->label('Liên hệ khẩn cấp')
                                            ->placeholder('Họ tên, quan hệ, số điện thoại')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Lương & Ngân hàng')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Section::make('Thông tin lương')
                                    ->schema([
                                        TextInput::make('base_salary')
                                            ->label('Lương cơ bản')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->prefix('đ'),

                                        TextInput::make('hourly_rate')
                                            ->label('Giá giờ công')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->prefix('đ'),

                                        TextInput::make('tax_code')
                                            ->label('Mã số thuế cá nhân')
                                            ->placeholder('VD: 0123456789'),
                                    ])
                                    ->columns(3),

                                Section::make('Thông tin ngân hàng')
                                    ->description('Thông tin tài khoản để chuyển lương')
                                    ->schema([
                                        TextInput::make('bank_name')
                                            ->label('Tên ngân hàng')
                                            ->placeholder('VD: Vietcombank, BIDV, Techcombank'),

                                        TextInput::make('bank_branch')
                                            ->label('Chi nhánh')
                                            ->placeholder('VD: Chi nhánh Hà Nội'),

                                        TextInput::make('bank_account_number')
                                            ->label('Số tài khoản')
                                            ->placeholder('Nhập số tài khoản'),

                                        TextInput::make('bank_account_name')
                                            ->label('Chủ tài khoản')
                                            ->placeholder('Tên chủ tài khoản (viết hoa, không dấu)'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Công việc')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                DatePicker::make('joined_at')
                                    ->label('Ngày vào làm'),

                                DatePicker::make('left_at')
                                    ->label('Ngày nghỉ việc')
                                    ->afterOrEqual('joined_at'),

                                Textarea::make('note')
                                    ->label('Ghi chú')
                                    ->placeholder('Ghi chú về nhân viên...')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
