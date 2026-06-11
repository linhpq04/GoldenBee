<?php

namespace App\Filament\Admin\Resources\Supports\Schemas;

use Filament\Schemas\Schema;

class SupportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
