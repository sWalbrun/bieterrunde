<?php

use App\Enums\EnumRole;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Maps the spatie/laravel-permission role assignments onto the new
 * {@link User::COL_ROLE} column. Precedence: super_admin > admin > member.
 * Users holding only direct permissions were acting admins (the panel used
 * to be open to everyone) and are therefore promoted to admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('model_has_roles')) {
            return;
        }

        $directPermissionHolders = DB::table('model_has_permissions')
            ->where('model_type', '=', User::class)
            ->pluck('model_id');
        if ($directPermissionHolders->isNotEmpty()) {
            Log::info(
                'Promoting users with direct permissions to admin',
                ['userIds' => $directPermissionHolders->all()]
            );
            $this->setRole($directPermissionHolders->all(), EnumRole::ADMIN);
        }

        $this->setRole($this->usersWithRole('admin'), EnumRole::ADMIN);
        $this->setRole($this->usersWithRole('super_admin'), EnumRole::SUPER_ADMIN);
    }

    public function down(): void
    {
        // Nothing to restore: the spatie tables are untouched by this migration.
    }

    private function usersWithRole(string $roleName): array
    {
        return DB::table('model_has_roles')
            ->join('role', 'role.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', '=', User::class)
            ->where('role.name', '=', $roleName)
            ->pluck('model_has_roles.model_id')
            ->all();
    }

    private function setRole(array $userIds, EnumRole $role): void
    {
        if (empty($userIds)) {
            return;
        }

        DB::table(User::TABLE)
            ->whereIn(User::COL_ID, $userIds)
            ->update([User::COL_ROLE => $role->value]);
    }
};
