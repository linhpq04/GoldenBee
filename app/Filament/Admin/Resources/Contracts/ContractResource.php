<?php

namespace App\Filament\Admin\Resources\Contracts;

use App\Filament\Admin\Resources\Contracts\Pages\CreateContract;
use App\Filament\Admin\Resources\Contracts\Pages\EditContract;
use App\Filament\Admin\Resources\Contracts\Pages\ListContracts;
use App\Filament\Admin\Resources\Contracts\Schemas\ContractForm;
use App\Filament\Admin\Resources\Contracts\Tables\ContractsTable;
use App\Models\Contract;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;
    protected static string|UnitEnum|null $navigationGroup = 'Bán hàng';
    protected static ?string $navigationLabel = 'Hợp đồng';
    protected static ?string $modelLabel = 'hợp đồng';
    protected static ?string $pluralModelLabel = 'hợp đồng';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    public static function form(Schema $schema): Schema
    {
        return ContractForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContractsTable::configure($table);
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
            'index' => ListContracts::route('/'),
            'create' => CreateContract::route('/create'),
            'edit' => EditContract::route('/{record}/edit'),
        ];
    }
}
