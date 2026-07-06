<?php

use App\Enums\EnumRole;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\WelcomeTenantAdmin;
use App\Tenancy\TenantProvisioner;
use Illuminate\Support\Facades\Notification;

it('provisions a tenant with its first admin user', function () {
    Notification::fake();

    $tenant = (new TenantProvisioner)->provision('neue-solawi', 'Maria', 'maria@solawi.test');

    expect($tenant->id)->toBe('neue-solawi');

    /** @var User $admin */
    $admin = User::query()->where(User::COL_EMAIL, '=', 'maria@solawi.test')->firstOrFail();
    expect($admin->name)->toBe('Maria')
        ->and($admin->role)->toBe(EnumRole::ADMIN)
        ->and($admin->tenant_id)->toBe('neue-solawi');

    Notification::assertSentTo($admin, WelcomeTenantAdmin::class);
});

it('provisions a tenant without an admin user', function () {
    Notification::fake();

    (new TenantProvisioner)->provision('leere-solawi');

    expect(Tenant::query()->where(Tenant::COL_ID, '=', 'leere-solawi')->exists())->toBeTrue()
        ->and(User::query()->where(User::COL_FK_TENANT, '=', 'leere-solawi')->exists())->toBeFalse();

    Notification::assertNothingSent();
});

it('creates the admin via the tenants:create command', function () {
    Notification::fake();

    $this->artisan('tenants:create cli-solawi --admin-name=Sepp --admin-email=sepp@solawi.test')
        ->assertSuccessful();

    /** @var User $admin */
    $admin = User::query()->where(User::COL_EMAIL, '=', 'sepp@solawi.test')->firstOrFail();
    expect($admin->tenant_id)->toBe('cli-solawi')
        ->and($admin->role)->toBe(EnumRole::ADMIN);

    Notification::assertSentTo($admin, WelcomeTenantAdmin::class);
});
