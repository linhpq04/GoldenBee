<?php

namespace App\Filament\Admin\Resources\Supports;

use App\Filament\Admin\Resources\Supports\Pages\CreateSupport;
use App\Filament\Admin\Resources\Supports\Pages\EditSupport;
use App\Filament\Admin\Resources\Supports\Pages\ListSupports;
use App\Filament\Admin\Resources\Supports\Schemas\SupportForm;
use App\Filament\Admin\Resources\Supports\Tables\SupportsTable;
use App\Models\SupportTicket;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SupportResource extends Resource
{
    protected static ?string $model = SupportTicket::class;
    protected static string|UnitEnum|null $navigationGroup = 'Dịch vụ khách hàng';
    protected static ?string $navigationLabel = 'Hỗ trợ';
    protected static ?string $modelLabel = 'ticket hỗ trợ';
    protected static ?string $pluralModelLabel = 'ticket hỗ trợ';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    public static function form(Schema $schema): Schema
    {
        return SupportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupportsTable::configure($table);
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
            'index' => ListSupports::route('/'),
            'create' => CreateSupport::route('/create'),
            'edit' => EditSupport::route('/{record}/edit'),
        ];
    }
}
