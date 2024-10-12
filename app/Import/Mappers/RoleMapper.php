<?php

namespace App\Import\Mappers;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use SWalbrun\FilamentModelImport\Import\ModelMapping\BaseMapper;

class RoleMapper extends BaseMapper
{
    public function __construct()
    {
        parent::__construct(new Role);
    }

    public function uniqueColumns(): array
    {
        return [
            'name',
        ];
    }

    public function propertyMapping(): Collection
    {
        return collect([
            'name' => '/^Rolle$/i',
        ]);
    }
}
