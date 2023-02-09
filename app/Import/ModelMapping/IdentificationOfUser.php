<?php

namespace App\Import\ModelMapping;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class IdentificationOfUser extends IdentificationOf implements AssociationOf
{
    public function __construct()
    {
        parent::__construct(new User());
    }

    public function uniqueColumns(): array
    {
        return [
            User::COL_EMAIL
        ];
    }

    public function propertyMapping(): Collection
    {
        return collect([
            User::COL_NAME => '/Benutzername/i',
            User::COL_EMAIL => '/E-Mail/i',
            User::COL_JOIN_DATE => '/Beitrittsdatum/i',
            User::COL_CONTRIBUTION_GROUP => '/Beitragsgruppe/i',
            User::COL_COUNT_SHARES => '/Anzahl d(.|er) Anteile/i',
            User::COL_CREATED_AT => '/Angelegt am/i',
        ]);
    }

    public function saving(Model $user): void
    {
        if (!$user instanceof User) {
            // Should never happen
            return;
        }

        if (!$user->exists) {
            // Since this is a new user, we set a random password to make sure the admin does not know the password
            $user->password = Hash::make(Str::random(10));
        }
    }

    public function associationOfClosures(): Collection
    {
        return collect([
            fn (User $user, Role $role) => $user->assignRole($role),
        ]);
    }
}
