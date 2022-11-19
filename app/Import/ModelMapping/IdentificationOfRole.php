<?php

namespace App\Import\ModelMapping;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class IdentificationOfRole extends IdentificationOf
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
        ]);
    }
}
