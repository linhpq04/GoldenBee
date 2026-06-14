<?php

namespace App\Filament\Admin\Resources\Customers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Thông tin chính')
                    ->description('Thông tin liên hệ và chi tiết khách hàng')
                    ->columnSpan(1)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Tên khách hàng / Người liên hệ')
                            ->placeholder('Họ và tên đầy đủ')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('company_name')
                            ->label('Tên công ty')
                            ->placeholder('Tên công ty (nếu là khách hàng doanh nghiệp)')
                            ->maxLength(255)
                            ->required(fn(callable $get) => $get('type') === 'Công ty')
                            ->hidden(fn(callable $get) => $get('type') === 'Cá nhân')
                            ->columnSpanFull(),

                        TextInput::make('email')
                            ->label('Email')
                            ->placeholder('email@example.com')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Số điện thoại')
                            ->placeholder('0912345678')
                            ->tel()
                            ->numeric()
                            ->maxLength(10),

                        TextInput::make('tax_code')
                            ->label('Mã số thuế')
                            ->placeholder('VD: 0123456789')
                            ->maxLength(20)
                            ->columnSpanFull(),

                        TextInput::make('website')
                            ->label('Website')
                            ->placeholder('https://example.com')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('address')
                            ->label('Địa chỉ')
                            ->placeholder('Địa chỉ đầy đủ')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Phân loại & Nguồn')
                    ->description('Trạng thái, nguồn khách hàng và thông tin bổ sung')
                    ->columnSpan(1)
                    ->columns(2)
                    ->schema([
                        Select::make('type')
                            ->label('Loại khách hàng')
                            ->required()
                            ->options([
                                'Cá nhân' => 'Cá nhân',
                                'Công ty' => 'Công ty',
                            ])
                            ->default('Cá nhân')
                            ->live(),

                        Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->options([
                                'Tiềm năng' => 'Tiềm năng',
                                'Đang hoạt động' => 'Đang hoạt động',
                                'Ngừng hoạt động' => 'Ngừng hoạt động',
                            ])
                            ->default('Tiềm năng'),

                        Select::make('source')
                            ->label('Nguồn khách hàng')
                            ->placeholder('Chọn một tùy chọn')
                            ->options([
                                'Website' => 'Website',
                                'Google / SEO' => 'Google / SEO',
                                'Facebook' => 'Facebook',
                                'Zalo' => 'Zalo',
                                'Giới thiệu' => 'Giới thiệu',
                                'Đối tác' => 'Đối tác',
                                'Khác' => 'Khác',
                            ])
                            ->helperText('Khách hàng biết đến công ty từ đâu?')
                            ->columnSpanFull(),

                        TextInput::make('source_detail')
                            ->label('Chi tiết nguồn')
                            ->placeholder('VD: Fanpage ABC, Quảng cáo XYZ, Anh Nguyễn giới thiệu...')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('industry')
                            ->label('Ngành nghề')
                            ->placeholder('VD: Công nghệ thông tin...')
                            ->maxLength(50),

                        TextInput::make('region')
                            ->label('Khu vực')
                            ->placeholder('VD: Hà Nội, TP.HCM...')
                            ->maxLength(50),

                        Textarea::make('note')
                            ->label('Ghi chú')
                            ->placeholder('Thông tin thêm về khách hàng...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}