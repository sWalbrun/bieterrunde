<?php

namespace __Tests__\ModelMappings;

use App\Import\ModelMapping\IdentificationOf;
use Illuminate\Support\Collection;

class IdentificationOfBlog extends IdentificationOf
{

    public function propertyMapping(): Collection
    {
        return collect([
            'blogName' => '/BlogName/i',
        ]);
    }

    public function uniqueColumns(): array
    {
        return [];
    }
}
