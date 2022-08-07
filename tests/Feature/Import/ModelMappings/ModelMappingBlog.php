<?php

namespace Tests\Feature\Import\ModelMappings;

use App\Import\ModelMapping\ModelMapping;
use Illuminate\Support\Collection;

class ModelMappingBlog extends ModelMapping
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
