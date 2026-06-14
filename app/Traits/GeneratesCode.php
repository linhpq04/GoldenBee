<?php

namespace App\Traits;

trait GeneratesCode
{
    public static function generateCode(): string
    {
        $datePart = now()->format('dmy');
        $prefix = static::$codePrefix . '-' . $datePart;

        $last = static::where('code', 'like', $prefix . '-%')
            ->orderByDesc('code')
            ->value('code');

        $next = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . '-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}
