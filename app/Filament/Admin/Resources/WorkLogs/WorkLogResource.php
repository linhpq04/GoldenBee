<?php

namespace App\Filament\Admin\Resources\WorkLogs;

use App\Filament\Admin\Resources\WorkLogs\Pages\CreateWorkLog;
use App\Filament\Admin\Resources\WorkLogs\Pages\EditWorkLog;
use App\Filament\Admin\Resources\WorkLogs\Pages\ListWorkLogs;
use App\Filament\Admin\Resources\WorkLogs\Schemas\WorkLogForm;
use App\Filament\Admin\Resources\WorkLogs\Tables\WorkLogsTable;
use App\Models\WorkLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WorkLogResource extends Resource
{
    protected static ?string $model = WorkLog::class;
    protected static string|UnitEnum|null $navigationGroup = 'Quản lý dự án';
    protected static ?string $navigationLabel = 'Chấm công';
    protected static ?string $modelLabel = 'bản ghi công';
    protected static ?string $pluralModelLabel = 'chấm công';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    public static function form(Schema $schema): Schema
    {
        return WorkLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkLogsTable::configure($table);
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
            'index' => ListWorkLogs::route('/'),
            'create' => CreateWorkLog::route('/create'),
            'edit' => EditWorkLog::route('/{record}/edit'),
        ];
    }
}
