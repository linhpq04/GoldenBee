<?php

namespace App\Filament\Admin\Resources\Tasks\Schemas;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                // ── CỘT TRÁI (span 2): Thông tin công việc ───────────
                Section::make('Thông tin công việc')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        Select::make('project_id')
                            ->label('Dự án')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('parent_task_id', null))
                            ->columnSpanFull(),

                        TextInput::make('title')
                            ->label('Tiêu đề công việc')
                            ->placeholder('VD: Thiết kế giao diện trang chủ')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('task_type')
                            ->label('Loại công việc')
                            ->options([
                                'Thiết kế' => 'Thiết kế',
                                'Lập trình' => 'Lập trình',
                                'Kiểm thử' => 'Kiểm thử',
                                'Nội dung' => 'Nội dung',
                                'SEO' => 'SEO',
                                'Tư vấn' => 'Tư vấn',
                                'Họp' => 'Họp',
                                'Khác' => 'Khác',
                            ])
                            ->columnSpan(1),

                        Select::make('parent_task_id')
                            ->label('Task cha')
                            ->options(function (callable $get, $record) {
                                $projectId = $get('project_id');
                                if (!$projectId)
                                    return [];
                                return Task::where('project_id', $projectId)
                                    ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                                    ->whereNull('parent_task_id')
                                    ->pluck('title', 'id');
                            })
                            ->searchable()
                            ->placeholder('Không có (task gốc)')
                            ->helperText('Chọn nếu đây là sub-task')
                            ->columnSpan(1),

                        Textarea::make('description')
                            ->label('Mô tả chi tiết')
                            ->placeholder('Mô tả yêu cầu, tiêu chí hoàn thành...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                // ── CỘT PHẢI (span 1): Phân loại & Người thực hiện ───
                Section::make('Phân công')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('assignee_id')
                            ->label('Người thực hiện')
                            ->options(fn() => User::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Chưa phân công'),

                        Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->options([
                                'Chưa làm' => 'Chưa làm',
                                'Đang làm' => 'Đang làm',
                                'Đang review' => 'Đang review',
                                'Hoàn thành' => 'Hoàn thành',
                                'Tạm dừng' => 'Tạm dừng',
                            ])
                            ->default('Chưa làm')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'Hoàn thành') {
                                    $set('completed_date', now()->format('Y-m-d'));
                                } else {
                                    $set('completed_date', null);
                                }
                            }),

                        Select::make('priority')
                            ->label('Độ ưu tiên')
                            ->required()
                            ->options([
                                'Thấp' => 'Thấp',
                                'Trung bình' => 'Trung bình',
                                'Cao' => 'Cao',
                                'Khẩn cấp' => 'Khẩn cấp',
                            ])
                            ->default('Trung bình'),
                    ]),

                // ── FULL WIDTH: Thời gian ─────────────────────────────
                Section::make('Thời gian')
                    ->columnSpan(3)
                    ->columns(4)
                    ->schema([
                        TextInput::make('estimated_hours')
                            ->label('Giờ ước tính')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.5)
                            ->suffix('giờ')
                            ->placeholder('0')
                            ->columnSpan(1),

                        DatePicker::make('start_date')
                            ->label('Ngày bắt đầu')
                            ->displayFormat('d/m/Y')
                            ->default(today())
                            ->columnSpan(1),

                        DatePicker::make('due_date')
                            ->label('Hạn hoàn thành')
                            ->displayFormat('d/m/Y')
                            ->afterOrEqual('start_date')
                            ->live()
                            ->helperText(fn($state) => $state && Carbon::parse($state)->isPast()
                                ? '⚠️ Hạn này đã qua'
                                : null)
                            ->columnSpan(1),

                        DatePicker::make('completed_date')
                            ->label('Ngày hoàn thành thực tế')
                            ->displayFormat('d/m/Y')
                            ->helperText('Tự điền khi chuyển sang Hoàn thành')
                            ->columnSpan(1),
                    ]),
            ]);

    }
}
