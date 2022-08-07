<?php

namespace Tests\Feature\Import\ModelMappings;

use App\Import\ModelMapping\ModelMapping;
use Illuminate\Support\Collection;

class ModelMappingPost extends ModelMapping
{
    public static $hasHookBeenCalled = false;

    public function propertyMapping(): Collection
    {
        return collect(
            [
                'property' => '/Property/i'
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
           ModelMappingBlog::class => fn (self $post, ModelMappingBlog $blog) => static::$hasHookBeenCalled = true
        ];
    }
}
