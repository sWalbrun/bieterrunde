<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;

/**
 * This command is creating a new tenant in case the requested one is not existing yet.
 */
class CreateTenantCommand extends Command
{
    public const TENANT_ID = 'tenant';
    public const SIGNATURE_WITHOUT_PARAMS = 'tenants:create';
    public const SIGNATURE = self::SIGNATURE_WITHOUT_PARAMS . ' {' . self::TENANT_ID . '?}';

    protected $signature = self::SIGNATURE;

    protected $description = 'Create and initialize a new tenant';

    /**
     * @return int
     *
     * @throws TenantCouldNotBeIdentifiedById
     */
    public function handle(): int
    {
        $tenantId = $this->argument(self::TENANT_ID) ?? $this->ask('Please provide an unique tenant identifier');

        if (Tenant::query()->where(Tenant::COL_ID, '=', $tenantId)->exists()) {
            $this->error('This tenant is already existing!');

            return self::FAILURE;
        }

        $tenant = Tenant::query()->create([Tenant::COL_ID => $tenantId]);

        tenancy()->initialize($tenant);

        // Just to make sure the migrations get executed
        Artisan::call('migrate --force');
        tenancy()->end();
        $this->info("Congratulation! Your new tenant ($tenantId) is ready for use!");
        return self::SUCCESS;
    }
}
