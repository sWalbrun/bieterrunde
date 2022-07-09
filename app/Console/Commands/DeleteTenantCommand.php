<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

/**
 * This command is deleting an existing tenant.
 */
class DeleteTenantCommand extends Command
{
    public const TENANT_ID = 'tenant';
    public const SIGNATURE_WITHOUT_PARAMS = 'tenants:delete';
    public const SIGNATURE = self::SIGNATURE_WITHOUT_PARAMS . ' {' . self::TENANT_ID . '?}';

    protected $signature = self::SIGNATURE;

    protected $description = 'Delete the given tenant';

    public function handle(): int
    {
        $tenantId = $this->argument(self::TENANT_ID) ?? $this->ask('Please provide an unique tenant identifier');

        $builder = Tenant::query()->where(Tenant::COL_ID, '=', $tenantId);
        if ($builder->doesntExist()) {
            $this->error(
                'Only those tenants (' . Tenant::query()->pluck(Tenant::COL_ID)->implode(', ') . ')  are existing!'
            );

            return self::FAILURE;
        }

        if (!$this->confirm('Do you confirm? The whole database will be deleted.')) {
            $this->info('Phew. Nothing has been deleted');
            return self::SUCCESS;
        }

        $builder->delete();
        $this->info("Tenant ($tenantId) has been deleted.");

        return self::SUCCESS;
    }
}
