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

/**
 * Focused session: only super admins may manage tenants. Everyone else —
 * members and plain admins — is locked out of every management capability
 * (resource gate, list/create pages, create and delete actions).
 */

/** Logs in a user of the given role, belonging to the given home tenant. */
function actingAsRole(EnumRole $role, Tenant $tenant): User
{
    /** @var User $user */
    $user = User::factory()
        ->state([User::COL_ROLE => $role, User::COL_FK_TENANT => $tenant->id])
        ->create();
    test()->actingAs($user);

    return $user;
}

beforeEach(function () {
    // A real tenant is required so the cookie based tenancy can initialize
    $this->tenant = Tenant::query()->create([Tenant::COL_ID => 'home']);
});

describe('non super admins cannot manage tenants', function () {
    it('denies the resource gate for every management capability', function (EnumRole $role) {
        actingAsRole($role, $this->tenant);

        expect(TenantResource::canViewAny())->toBeFalse()
            ->and(TenantResource::canCreate())->toBeFalse()
            ->and(TenantResource::canDeleteAny())->toBeFalse()
            ->and(TenantResource::canDelete($this->tenant))->toBeFalse();
    })->with([
        'admin' => EnumRole::ADMIN,
        'member' => EnumRole::MEMBER,
    ]);

    it('forbids access to the tenant list and create pages', function (EnumRole $role) {
        actingAsRole($role, $this->tenant);

        $this->call('GET', TenantResource::getUrl(), cookies: [SetTenantCookie::TENANT_ID => $this->tenant->id])
            ->assertForbidden();
        $this->call('GET', TenantResource::getUrl('create'), cookies: [SetTenantCookie::TENANT_ID => $this->tenant->id])
            ->assertForbidden();
    })->with([
        'admin' => EnumRole::ADMIN,
        'member' => EnumRole::MEMBER,
    ]);

    it('cannot create a tenant even by driving the component directly', function () {
        Notification::fake();
        actingAsRole(EnumRole::ADMIN, $this->tenant);

        // The unauthorized create action is halted by the framework; the point
        // is that no tenant and no admin user are ever persisted.
        rescue(fn () => livewire(CreateTenant::class)
            ->fillForm(['id' => 'sneaky-solawi', 'admin_name' => 'Mallory', 'admin_email' => 'mallory@solawi.test'])
            ->call('create'), report: false);

        expect(Tenant::query()->where(Tenant::COL_ID, '=', 'sneaky-solawi')->exists())->toBeFalse();
        Notification::assertNothingSent();
    });

    it('cannot delete a tenant even by driving the component directly', function () {
        actingAsRole(EnumRole::ADMIN, $this->tenant);
        Tenant::query()->create([Tenant::COL_ID => 'victim']);

        rescue(fn () => livewire(ListTenants::class)
            ->callTableAction(DeleteAction::class, Tenant::query()->find('victim')), report: false);

        expect(Tenant::query()->where(Tenant::COL_ID, '=', 'victim')->exists())->toBeTrue();
    });
});

describe('super admins can manage tenants', function () {
    it('passes every management capability gate', function () {
        actingAsRole(EnumRole::SUPER_ADMIN, $this->tenant);

        expect(TenantResource::canViewAny())->toBeTrue()
            ->and(TenantResource::canCreate())->toBeTrue()
            ->and(TenantResource::canDeleteAny())->toBeTrue()
            ->and(TenantResource::canDelete($this->tenant))->toBeTrue();
    });

    it('reaches the list and create pages', function () {
        actingAsRole(EnumRole::SUPER_ADMIN, $this->tenant);

        $this->call('GET', TenantResource::getUrl(), cookies: [SetTenantCookie::TENANT_ID => $this->tenant->id])
            ->assertSuccessful();
        $this->call('GET', TenantResource::getUrl('create'), cookies: [SetTenantCookie::TENANT_ID => $this->tenant->id])
            ->assertSuccessful();
    });

    it('creates and deletes a tenant', function () {
        Notification::fake();
        actingAsRole(EnumRole::SUPER_ADMIN, $this->tenant);

        livewire(CreateTenant::class)
            ->fillForm([
                'id' => 'neue-solawi',
                'admin_name' => 'Maria',
                'admin_email' => 'maria@solawi.test',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Tenant::query()->where(Tenant::COL_ID, '=', 'neue-solawi')->exists())->toBeTrue();
        Notification::assertSentTo(
            User::query()->where(User::COL_EMAIL, '=', 'maria@solawi.test')->firstOrFail(),
            WelcomeTenantAdmin::class
        );

        livewire(ListTenants::class)
            ->callTableAction(DeleteAction::class, Tenant::query()->find('neue-solawi'));

        expect(Tenant::query()->where(Tenant::COL_ID, '=', 'neue-solawi')->exists())->toBeFalse();
    });
});
