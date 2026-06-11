<?php

namespace App\Filament\Admin\Resources\Tasks;

use App\Filament\Admin\Resources\Tasks\Pages\CreateTask;
use App\Filament\Admin\Resources\Tasks\Pages\EditTask;
use App\Filament\Admin\Resources\Tasks\Pages\ListTasks;
use App\Filament\Admin\Resources\Tasks\Schemas\TaskForm;
use App\Filament\Admin\Resources\Tasks\Tables\TasksTable;
use App\Models\Task;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;
    protected static string|UnitEnum|null $navigationGroup = 'Quản lý dự án';
    protected static ?string $navigationLabel = 'Công việc';
    protected static ?string $modelLabel = 'công việc';
    protected static ?string $pluralModelLabel = 'công việc';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function form(Schema $schema): Schema
    {
        return TaskForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TasksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTasks::route('/'),
            'create' => CreateTask::route('/create'),
            'edit' => EditTask::route('/{record}/edit'),
        ];
    }
}
