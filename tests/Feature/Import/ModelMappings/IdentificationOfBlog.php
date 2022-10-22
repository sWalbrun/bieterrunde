<?php

namespace Tests\Feature\Import\ModelMappings;

use App\Import\ModelMapping\IdentificationOf;
use Illuminate\Support\Collection;

class IdentificationOfBlog extends IdentificationOf
{

    public function propertyMapping(): Collection
    {
        return collect();
    }

    public function uniqueColumns(): array
    {
        return [];
    }
}
