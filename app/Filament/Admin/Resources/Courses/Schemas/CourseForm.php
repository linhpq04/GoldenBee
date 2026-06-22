<?php

namespace App\Filament\Admin\Resources\Courses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                // ── CỘT TRÁI: Thông tin khóa học ─────────────────────
                Section::make('Thông tin khóa học')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('title')
                            ->label('Tên khóa học')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('VD: Khóa học thiết kế web cơ bản'),

                        Textarea::make('description')
                            ->label('Mô tả')
                            ->placeholder('Mô tả nội dung, mục tiêu khóa học...')
                            ->rows(5),
                    ]),

                // ── CỘT PHẢI: Phân loại & Quy mô ─────────────────────
                Section::make('Phân loại & Quy mô')
                    ->columnSpan(1)
                    ->columns(2)
                    ->schema([
                        Select::make('format')
                            ->label('Hình thức')
                            ->required()
                            ->options([
                                '1-1' => '1-1 (Cá nhân)',
                                'Nhóm' => 'Nhóm',
                                'Trực tuyến' => 'Trực tuyến',
                                'Tại lớp' => 'Tại lớp',
                            ])
                            ->default('Nhóm')
                            ->columnSpan(1),

                        Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->options([
                                'Bản nháp' => 'Bản nháp',
                                'Đang mở' => 'Đang mở',
                                'Hoàn thành' => 'Hoàn thành',
                                'Đã hủy' => 'Đã hủy',
                            ])
                            ->default('Bản nháp')
                            ->columnSpan(1),

                        TextInput::make('max_student')
                            ->label('Số học viên tối đa')
                            ->numeric()
                            ->minValue(1)
                            ->suffix('người')
                            ->placeholder('Không giới hạn')
                            ->helperText('Để trống = không giới hạn')
                            ->columnSpan(1),

                        TextInput::make('duration_hours')
                            ->label('Thời lượng')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.5)
                            ->suffix('giờ')
                            ->placeholder('0')
                            ->columnSpan(1),
                    ]),

                // ── FULL WIDTH: Giá khóa học ──────────────────────────
                Section::make('Giá khóa học')
                    ->columnSpan(2)
                    ->columns(1)
                    ->schema([
                        TextInput::make('price')
                            ->label('Học phí/học viên')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->prefix('VNĐ')
                            ->helperText('Học phí tính trên mỗi học viên'),
                    ]),
            ]);
    }
}
