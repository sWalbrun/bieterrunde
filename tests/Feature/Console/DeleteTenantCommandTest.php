<?php

namespace Tests\Feature\Console;

use App\Console\Commands\CreateTenantCommand;
use App\Console\Commands\DeleteTenantCommand;
use App\Models\Tenant;
use Tests\TestCase;

/**
 * Those tests make sure the deletion of a tenant via command line is working as expected.
 */
class DeleteTenantCommandTest extends TestCase
{
    /**
     * This test ensures the default case by checking if a tenant gets deleted successfully.
     */
    public function testTenantTenantSuccessfully(): void
    {
        $tenantId = 'Tenor';
        Tenant::query()->create([Tenant::COL_ID => $tenantId]);

        $this->artisan(DeleteTenantCommand::SIGNATURE_WITHOUT_PARAMS, [CreateTenantCommand::TENANT_ID => $tenantId])
            ->expectsConfirmation('Do you confirm? The whole database will be deleted.', 'yes')
            ->assertSuccessful();

        $this->assertTrue(Tenant::query()->doesntExist());
    }

    /**
     * This test ensures the default case by checking if a tenant gets deleted successfully with prompting.
     */
    public function testTenantTenantSuccessfullyWithPrompting(): void
    {
        $tenantId = 'Tenor';
        Tenant::query()->create([Tenant::COL_ID => $tenantId]);

        $this->artisan(DeleteTenantCommand::SIGNATURE_WITHOUT_PARAMS)
            ->expectsQuestion('Please provide an unique tenant identifier', $tenantId)
            ->expectsConfirmation('Do you confirm? The whole database will be deleted.', 'yes')
            ->assertSuccessful();

        $this->assertTrue(Tenant::query()->doesntExist());
    }

    /**
     * This test ensures no tenant gets deleted in case the confirmation has not been given.
     */
    public function testTenantTenantWithoutConfirmation(): void
    {
        $tenantId = 'Tenor';
        Tenant::query()->create([Tenant::COL_ID => $tenantId]);

        $this->artisan(DeleteTenantCommand::SIGNATURE_WITHOUT_PARAMS, [CreateTenantCommand::TENANT_ID => $tenantId])
            ->expectsConfirmation('Do you confirm? The whole database will be deleted.', 'no')
            ->expectsOutput('Phew. Nothing has been deleted')
            ->assertSuccessful();

        $this->assertTrue(Tenant::query()->exists());
    }

    /**
     * This test makes sure no tenant gets deleted which is not existing beforehand.
     */
    public function testDeleteNonExistingTenant(): void
    {
        Tenant::query()->create([Tenant::COL_ID => 'anotherTenant']);
        Tenant::query()->create([Tenant::COL_ID => 'andAFurtherOne']);

        $tenantId = 'aNewOne';

        $this->assertDatabaseCount(Tenant::TABLE, 2);
        $this->artisan(DeleteTenantCommand::SIGNATURE_WITHOUT_PARAMS, [DeleteTenantCommand::TENANT_ID => $tenantId])
            ->expectsOutput('Only those tenants ('.Tenant::query()->pluck(Tenant::COL_ID)->implode(', ').')  are existing!')
            ->assertFailed();
        $this->assertDatabaseCount(Tenant::TABLE, 2);
    }
}
