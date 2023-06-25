<?php

namespace App\Filament\Utils;

use BenSampo\Enum\Enum;
use Illuminate\Support\Collection;

class ForFilamentTranslator
{
    public static function enum(array $enums): Collection
    {
        return collect($enums)->mapWithKeys(
            fn (Enum $value) => [$value->key => trans($value->value)]
        );
    }
}
