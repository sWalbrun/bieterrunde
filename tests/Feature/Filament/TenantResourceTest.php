<?php

use App\Enums\EnumRole;
use App\Filament\Resources\TenantResource;
use App\Filament\Resources\TenantResource\Pages\CreateTenant;
use App\Filament\Resources\TenantResource\Pages\ListTenants;
use App\Jobs\SetTenantCookie;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\WelcomeTenantAdmin;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Support\Facades\Notification;

use function Pest\Livewire\livewire;

it('denies plain admins access to the tenant resource', function () {
    /** @var Tenant $tenant */
    $tenant = Tenant::query()->create([Tenant::COL_ID => 'foo']);
    /** @var User $admin */
    $admin = User::factory()->admin()->create([User::COL_FK_TENANT => $tenant->id]);

    $this->actingAs($admin);
    $this->call('GET', TenantResource::getUrl(), cookies: [SetTenantCookie::TENANT_ID => $tenant->id])
        ->assertForbidden();
});

it('allows super admins to list tenants', function () {
    /** @var Tenant $tenant */
    $tenant = Tenant::query()->create([Tenant::COL_ID => 'foo']);
    /** @var User $superAdmin */
    $superAdmin = User::factory()->superAdmin()->create([User::COL_FK_TENANT => $tenant->id]);

    $this->actingAs($superAdmin);
    $this->call('GET', TenantResource::getUrl(), cookies: [SetTenantCookie::TENANT_ID => $tenant->id])
        ->assertSuccessful();

    livewire(ListTenants::class)->assertCanSeeTableRecords([$tenant]);
});

it('provisions a tenant with admin via the create page', function () {
    Notification::fake();

    livewire(CreateTenant::class)
        ->fillForm([
            'id' => 'neue-solawi',
            'admin_name' => 'Maria',
            'admin_email' => 'maria@solawi.test',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Tenant::query()->where(Tenant::COL_ID, '=', 'neue-solawi')->exists())->toBeTrue();

    /** @var User $admin */
    $admin = User::query()->where(User::COL_EMAIL, '=', 'maria@solawi.test')->firstOrFail();
    expect($admin->role)->toBe(EnumRole::ADMIN)
        ->and($admin->tenant_id)->toBe('neue-solawi');

    Notification::assertSentTo($admin, WelcomeTenantAdmin::class);
});

it('rejects an invalid tenant identifier', function () {
    livewire(CreateTenant::class)
        ->fillForm([
            'id' => 'Ünsinn ID!',
            'admin_name' => 'Maria',
            'admin_email' => 'maria@solawi.test',
        ])
        ->call('create')
        ->assertHasFormErrors(['id']);
});

it('deletes a tenant including its data via the table action', function () {
    /** @var Tenant $tenant */
    $tenant = Tenant::query()->create([Tenant::COL_ID => 'doomed']);
    $tenant->run(fn () => User::factory()->create());

    livewire(ListTenants::class)
        ->callTableAction(DeleteAction::class, $tenant);

    expect(Tenant::query()->where(Tenant::COL_ID, '=', 'doomed')->exists())->toBeFalse()
        ->and(User::query()->withoutGlobalScopes()->where(User::COL_FK_TENANT, '=', 'doomed')->exists())->toBeFalse();
});
