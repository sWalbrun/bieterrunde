<?php

namespace App\Import\Mappers;

use App\Enums\EnumRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SWalbrun\FilamentModelImport\Import\ModelMapping\BaseMapper;

class UserMapper extends BaseMapper
{
    public function __construct()
    {
        parent::__construct(new User);
    }

    public function uniqueColumns(): array
    {
        return [
            User::COL_EMAIL,
        ];
    }

    public function propertyMapping(): Collection
    {
        // The role is intentionally not mapped from a column — it is assigned
        // statically in saving() (github issue #7).
        return collect([
            User::COL_NAME => '/Benutzername/i',
            User::COL_EMAIL => '/E-Mail/i',
            User::COL_JOIN_DATE => '/Beitrittsdatum/i',
            User::COL_CONTRIBUTION_GROUP => '/Beitragsgruppe/i',
            User::COL_CREATED_AT => '/Angelegt am/i',
        ]);
    }

    /**
     * @param  User  $model
     */
    public function saving(Model $model): void
    {
        // Imports only ever create members; the role is set statically here,
        // never read from the file. Existing users keep their role so a
        // re-import cannot demote an admin (github issue #7).
        if (! $model->exists) {
            $model->role = EnumRole::MEMBER;
        }
    }
}
