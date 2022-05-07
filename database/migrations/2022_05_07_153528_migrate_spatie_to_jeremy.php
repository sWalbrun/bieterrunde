<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use jeremykenedy\LaravelRoles\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MigrateSpatieToJeremy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        User::query()->each(function (User $user) {
            $user->morphToMany(
                Spatie\Permission\Models\Role::class,
                'model',
                'model_has_roles',
                'model_id',
                PermissionRegistrar::$pivotRole
            )->pluck('name')->each(function (string $roleName) use ($user) {
                /** @var Role $role */
                $role = Role::query()->updateOrCreate([
                    'name' => $roleName,
                    'slug' => Str::lower($roleName),
                ]);
                $user->attachRole($role);
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
