<?php

namespace App\Filament\Admin\Resources\Salaries;

use App\Filament\Admin\Resources\Salaries\Pages\CreateSalary;
use App\Filament\Admin\Resources\Salaries\Pages\EditSalary;
use App\Filament\Admin\Resources\Salaries\Pages\ListSalaries;
use App\Filament\Admin\Resources\Salaries\Schemas\SalaryForm;
use App\Filament\Admin\Resources\Salaries\Tables\SalariesTable;
use App\Models\SalaryPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SalaryResource extends Resource
{
    protected static ?string $model = SalaryPayment::class;
    protected static string|UnitEnum|null $navigationGroup = 'Nhân sự';
    protected static ?string $navigationLabel = 'Lịch sử trả lương';
    protected static ?string $modelLabel = 'phiếu lương';
    protected static ?string $pluralModelLabel = 'phiếu lương';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    public static function form(Schema $schema): Schema
    {
        return SalaryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalariesTable::configure($table);
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
            'index' => ListSalaries::route('/'),
            'create' => CreateSalary::route('/create'),
            'edit' => EditSalary::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) SalaryPayment::where('status', 'Chờ duyệt')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }
}
