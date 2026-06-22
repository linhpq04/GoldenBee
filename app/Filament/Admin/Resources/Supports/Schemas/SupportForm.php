<?php

namespace App\Filament\Admin\Resources\Supports\Schemas;

use App\Models\Project;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                // ── CỘT TRÁI (span 2): Thông tin chính ───────────────
                Section::make('Thông tin chính')
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

                        TextInput::make('title')
                            ->label('Tiêu đề')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Mô tả ngắn gọn vấn đề...')
                            ->columnSpanFull(),

                        RichEditor::make('description')
                            ->label('Mô tả chi tiết')
                            ->required()
                            ->placeholder('Mô tả chi tiết vấn đề, các bước tái hiện, kết quả mong đợi...')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                            ])
                            ->columnSpanFull(),

                        FileUpload::make('file_path')
                            ->label('File đính kèm')
                            ->multiple()
                            ->maxFiles(5)
                            ->maxSize(10240)
                            ->acceptedFileTypes([
                                'image/*',
                                'application/pdf',
                                'application/msword',
                                'application/zip',
                            ])
                            ->helperText('Tối đa 5 files, mỗi file 10MB. Hỗ trợ: ảnh, PDF, Word, ZIP')
                            ->columnSpanFull(),
                    ]),

                // ── CỘT PHẢI (span 1): Phân loại & Xử lý ────────────
                Section::make('Phân loại')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('category')
                            ->label('Danh mục')
                            ->required()
                            ->options([
                                'Chung' => '📋 Chung',
                                'Website' => '🌐 Website',
                                'Hosting' => '🖥️ Hosting',
                                'Tên miền' => '🔗 Tên miền',
                                'Email' => '📧 Email',
                                'Bảo mật' => '🔒 Bảo mật',
                                'Thanh toán' => '💳 Thanh toán',
                                'Khác' => '❓ Khác',
                            ])
                            ->default('Chung')
                            ->helperText('Chọn danh mục phù hợp'),

                        Select::make('priority')
                            ->label('Độ ưu tiên')
                            ->required()
                            ->options([
                                'Thấp' => '🔵 Thấp',
                                'Trung bình' => '🟡 Trung bình',
                                'Cao' => '🟠 Cao',
                                'Khẩn cấp' => '🔴 Khẩn cấp',
                            ])
                            ->default('Trung bình')
                            ->helperText('SLA: Khẩn cấp 4h, Cao 8h, Trung bình 24h'),
                    ]),

                Section::make('Xử lý')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->options([
                                'Mới' => '🆕 Mới',
                                'Đang xử lý' => '⚙️ Đang xử lý',
                                'Chờ phản hồi' => '⏳ Chờ phản hồi',
                                'Đã giải quyết' => '✅ Đã giải quyết',
                                'Đóng' => '🔒 Đóng',
                            ])
                            ->default('Mới'),

                        Select::make('assignee_id')
                            ->label('Người xử lý')
                            ->options(fn() => User::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Chưa phân công')
                            ->helperText('Để trống = tự động phân công'),
                    ]),

                // ── CỘT PHẢI (span 1): Thời gian ─────────────────────
                Section::make('Thời gian')
                    ->columnSpan(1)
                    ->collapsible()
                    ->schema([
                        DateTimePicker::make('started_at')
                            ->label('Giải quyết lúc')
                            ->displayFormat('d/m/Y H:i')
                            ->helperText('Tự động điền khi chuyển sang Đang xử lý'),

                        DateTimePicker::make('ended_at')
                            ->label('Đóng lúc')
                            ->displayFormat('d/m/Y H:i')
                            ->helperText('Tự động điền khi Đã giải quyết/Đóng'),
                    ]),
            ]);
    }
}
