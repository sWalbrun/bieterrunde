<?php

namespace App\Import\ModelMapping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
        ]);
    }

    public function preSaveHook(Model $role): void
    {
        if (!$role instanceof Role) {
            // Should never happen
            return;
        }
        $role->slug = Str::slug($role->name);
    }
}
