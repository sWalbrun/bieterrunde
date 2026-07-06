<?php

namespace App\Tenancy;

use App\Enums\EnumRole;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\WelcomeTenantAdmin;

/**
 * Creates new tenants — used by the create command, the tenant resource and
 * the test account approval. Since all tenants share one database no
 * migrations have to run here; the {@link \Stancl\Tenancy\Events\TenantCreated}
 * event takes care of the storage directories.
 */
class TenantProvisioner
{
    /**
     * Creates the tenant and optionally its first admin user, who receives
     * a welcome mail with a login link.
     */
    public function provision(string $tenantId, ?string $adminName = null, ?string $adminEmail = null): Tenant
    {
        /** @var Tenant $tenant */
        $tenant = Tenant::query()->create([Tenant::COL_ID => $tenantId]);

        if (isset($adminEmail)) {
            /** @var User $admin */
            $admin = $tenant->run(function () use ($adminName, $adminEmail) {
                $user = new User;
                $user->name = $adminName ?? $adminEmail;
                $user->email = $adminEmail;
                $user->role = EnumRole::ADMIN;
                $user->save();

                return $user;
            });

            $admin->notify(new WelcomeTenantAdmin($tenant));
        }

        return $tenant;
    }
}
