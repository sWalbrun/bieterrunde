<?php

namespace Tests\Feature\Console;

use App\Console\Commands\CreateTenantCommand;
use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;
use Tests\TestCase;

/**
 * Those tests make sure the creation of a tenant via command line is working as expected.
 */
class CreateTenantCommandTest extends TestCase
{
    /**
     * This test ensures the default case by checking if a tenant gets created successfully.
     *
     * @throws TenantCouldNotBeIdentifiedById
     */
    public function testCreateTenantSuccessfully(): void
    {
        $tenantId = 'Tenor';
        $this->artisan(CreateTenantCommand::SIGNATURE_WITHOUT_PARAMS, [CreateTenantCommand::TENANT_ID => $tenantId])
            ->assertSuccessful();

        $this->assertSuccessfulResponse($tenantId);
    }

    /**
     * This test ensures the default case by checking if a tenant gets created successfully with prompting the tenant id.
     *
     * @throws TenantCouldNotBeIdentifiedById
     */
    public function testCreateTenantSuccessfullyWithPrompting(): void
    {
        $tenantId = 'aNewOne';
        $this->artisan(CreateTenantCommand::SIGNATURE_WITHOUT_PARAMS)
            ->expectsQuestion('Please provide an unique tenant identifier', $tenantId)
            ->assertSuccessful();

        $this->assertSuccessfulResponse($tenantId);
    }

    /**
     * This test ensures no tenant gets created in case there is already one existing.
     */
    public function testCreateTenantAndFail(): void
    {
        $tenantId = 'aNewOne';

        Tenant::query()->create([Tenant::COL_ID => $tenantId]);

        $this->artisan(CreateTenantCommand::SIGNATURE_WITHOUT_PARAMS, [CreateTenantCommand::TENANT_ID => $tenantId])
            ->expectsOutput('This tenant is already existing!')
            ->assertFailed();
    }

    /**
     * @throws TenantCouldNotBeIdentifiedById
     */
    private function assertSuccessfulResponse(string $tenantId): void
    {
        $builder = Tenant::query()->where([Tenant::COL_ID => $tenantId]);
        $this->assertTrue($builder->exists());

        tenancy()->initialize($tenantId);
        $this->assertTrue(Schema::hasTable(Tenant::TABLE), 'The migrations did not succeed');
    }
}
