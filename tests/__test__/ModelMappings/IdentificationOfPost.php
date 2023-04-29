<?php

namespace __Tests__\ModelMappings;

use App\Import\ModelMapping\IdentificationOf;
use Illuminate\Support\Collection;

class IdentificationOfPost extends IdentificationOf
{
    public static bool $hasHookBeenCalled = false;

    public function propertyMapping(): Collection
    {
        return collect(
            [
                'postName' => '/PostName/i'
            ]
        );
    }

    public function uniqueColumns(): array
    {
        return [];
    }

    public function associationHooks(): array
    {
        return [

        ];
    }
}
