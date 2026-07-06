<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Tenancy\TenantProvisioner;
use Illuminate\Console\Command;

/**
 * This command is creating a new tenant in case the requested one is not existing yet.
 * All tenants share one database — central migrations run at deploy time.
 */
class CreateTenantCommand extends Command
{
    public const TENANT_ID = 'tenant';

    public const ADMIN_NAME = 'admin-name';

    public const ADMIN_EMAIL = 'admin-email';

    public const SIGNATURE_WITHOUT_PARAMS = 'tenants:create';

    public const SIGNATURE = self::SIGNATURE_WITHOUT_PARAMS
        .' {'.self::TENANT_ID.'?}'
        .' {--'.self::ADMIN_NAME.'=}'
        .' {--'.self::ADMIN_EMAIL.'= : If given, an admin user gets created and welcomed by mail}';

    protected $signature = self::SIGNATURE;

    protected $description = 'Create and initialize a new tenant';

    public function handle(TenantProvisioner $provisioner): int
    {
        $tenantId = $this->argument(self::TENANT_ID) ?? $this->ask('Please provide an unique tenant identifier');

        if (Tenant::query()->where(Tenant::COL_ID, '=', $tenantId)->exists()) {
            $this->error('This tenant is already existing!');

            return self::FAILURE;
        }

        $provisioner->provision(
            $tenantId,
            $this->option(self::ADMIN_NAME),
            $this->option(self::ADMIN_EMAIL),
        );

        $this->info("Congratulation! Your new tenant ($tenantId) is ready for use!");

        return self::SUCCESS;
    }
}
