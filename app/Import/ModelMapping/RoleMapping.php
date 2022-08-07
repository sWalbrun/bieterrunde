<?php

namespace App\Import\ModelMapping;

use Illuminate\Support\Collection;
use jeremykenedy\LaravelRoles\Models\Role;

class RoleMapping extends ModelMapping
{
    public function __construct()
    {
        parent::__construct(new Role());
    }

    public function uniqueColumns(): array
    {
        return [
            'name'
        ];
    }

    public function propertyMapping(): Collection
    {
        return collect([
            'name' => '/^Rolle$/i',
            'slug' => '/Slug Rolle/i'
        ]);
    }
}
