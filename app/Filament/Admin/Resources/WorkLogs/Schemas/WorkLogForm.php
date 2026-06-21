<?php

namespace App\Filament\Admin\Resources\WorkLogs\Schemas;

use App\Models\Task;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class WorkLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Thông tin chấm công')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('user_id')
                            ->label('Nhân viên')
                            ->options(fn() => User::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->default(fn() => Auth::id()),

                        DatePicker::make('work_date')
                            ->label('Ngày làm việc')
                            ->displayFormat('d/m/Y')
                            ->required()
                            ->default(today())
                            ->maxDate(today()),

                        TextInput::make('hours')
                            ->label('Số giờ')
                            ->numeric()
                            ->required()
                            ->minValue(0.5)
                            ->maxValue(24)
                            ->step(0.5)
                            ->suffix('giờ')
                            ->helperText('Tối thiểu 0.5h, tối đa 24h'),

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
                            ]),
                    ]),

                Section::make('Chi tiết')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('task_id')
                            ->label('Công việc liên quan')
                            ->options(fn() => Task::with('project')
                                ->whereNotIn('status', ['Hoàn thành', 'Tạm dừng'])
                                ->get()
                                ->mapWithKeys(fn($task) => [
                                    $task->id => ($task->project?->name
                                        ? '[' . $task->project->name . '] '
                                        : '') . $task->title
                                ]))
                            ->searchable()
                            ->placeholder('Không gắn với task cụ thể')
                            ->helperText('Chọn task đang thực hiện nếu có'),

                        Textarea::make('description')
                            ->label('Mô tả công việc đã làm')
                            ->placeholder('Mô tả chi tiết những gì đã làm trong ngày...')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
