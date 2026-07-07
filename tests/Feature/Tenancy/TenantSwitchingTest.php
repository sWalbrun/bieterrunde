<?php

use App\Filament\Resources\TenantResource\Pages\ListTenants;
use App\Jobs\SetTenantCookie;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;

use function Pest\Livewire\livewire;

it('lets super admins access another tenant via cookie', function () {
    /** @var Tenant $home */
    $home = Tenant::query()->create([Tenant::COL_ID => 'home']);
    /** @var Tenant $other */
    $other = Tenant::query()->create([Tenant::COL_ID => 'other']);

    /** @var User $superAdmin */
    $superAdmin = User::factory()->superAdmin()->create([User::COL_FK_TENANT => $home->id]);
    $this->actingAs($superAdmin);

    $this->call('GET', '/admin', cookies: [SetTenantCookie::TENANT_ID => $other->id])
        ->assertSuccessful();

    expect(auth()->check())->toBeTrue();
});

it('resolves a super admin as the auth user while a foreign tenant is active', function () {
    Tenant::query()->create([Tenant::COL_ID => 'home']);
    /** @var Tenant $other */
    $other = Tenant::query()->create([Tenant::COL_ID => 'other']);
    /** @var User $superAdmin */
    $superAdmin = User::factory()->superAdmin()->create([User::COL_FK_TENANT => 'home']);

    tenancy()->initialize($other);

    // Without the tenant-aware provider this returns null (user's tenant_id is 'home')
    $resolved = auth()->getProvider()->retrieveById($superAdmin->id);

    expect($resolved?->id)->toBe($superAdmin->id);
});

it('logs normal users out when the cookie points at a foreign tenant', function () {
    /** @var Tenant $home */
    $home = Tenant::query()->create([Tenant::COL_ID => 'home']);
    /** @var Tenant $other */
    $other = Tenant::query()->create([Tenant::COL_ID => 'other']);

    /** @var User $admin */
    $admin = User::factory()->admin()->create([User::COL_FK_TENANT => $home->id]);
    $this->actingAs($admin);

    $this->withoutExceptionHandling();
    expect(fn () => $this->call('GET', '/admin', cookies: [SetTenantCookie::TENANT_ID => $other->id]))
        ->toThrow(TenantCouldNotBeIdentifiedById::class);

    expect(auth()->check())->toBeFalse();
});

it('sets the tenant cookie on a real redirect when a super admin switches', function () {
    /** @var Tenant $home */
    $home = Tenant::query()->create([Tenant::COL_ID => 'home']);
    /** @var Tenant $other */
    $other = Tenant::query()->create([Tenant::COL_ID => 'other']);

    /** @var User $superAdmin */
    $superAdmin = User::factory()->superAdmin()->create([User::COL_FK_TENANT => $home->id]);
    $this->actingAs($superAdmin);
    // actingAs() fires Authenticated outside a request — discard that queued cookie artifact
    Cookie::flushQueuedCookies();

    $this->call('GET', route('tenant.switch', $other), cookies: [SetTenantCookie::TENANT_ID => $home->id])
        ->assertRedirect('/admin')
        // The tenant cookie is unencrypted (see EncryptCookies::$except)
        ->assertCookie(SetTenantCookie::TENANT_ID, $other->id, encrypted: false);
});

it('forbids non super admins from switching tenants', function () {
    /** @var Tenant $home */
    $home = Tenant::query()->create([Tenant::COL_ID => 'home']);
    /** @var Tenant $other */
    $other = Tenant::query()->create([Tenant::COL_ID => 'other']);

    /** @var User $admin */
    $admin = User::factory()->admin()->create([User::COL_FK_TENANT => $home->id]);
    $this->actingAs($admin);
    Cookie::flushQueuedCookies();

    $this->call('GET', route('tenant.switch', $other), cookies: [SetTenantCookie::TENANT_ID => $home->id])
        ->assertForbidden()
        ->assertCookieMissing(SetTenantCookie::TENANT_ID);
});

it('exposes the switch action as a link to the switch route', function () {
    /** @var Tenant $home */
    $home = Tenant::query()->create([Tenant::COL_ID => 'home']);
    /** @var Tenant $other */
    $other = Tenant::query()->create([Tenant::COL_ID => 'other']);
    $this->actingAs(User::factory()->superAdmin()->create([User::COL_FK_TENANT => $home->id]));

    livewire(ListTenants::class)
        ->assertTableActionHasUrl('switch', route('tenant.switch', $other), record: $other);
});

it('does not reset an existing tenant cookie on authenticated requests', function () {
    /** @var Tenant $home */
    $home = Tenant::query()->create([Tenant::COL_ID => 'home']);
    Tenant::query()->create([Tenant::COL_ID => 'other']);

    /** @var User $superAdmin */
    $superAdmin = User::factory()->superAdmin()->create([User::COL_FK_TENANT => $home->id]);
    $this->actingAs($superAdmin);
    // actingAs() fires Authenticated outside of a request — discard that artifact
    Cookie::flushQueuedCookies();

    $this->call('GET', '/admin', cookies: [SetTenantCookie::TENANT_ID => 'other']);

    // The listener must not queue the home tenant again — the switch would be lost
    expect(Cookie::queued(SetTenantCookie::TENANT_ID))->toBeNull();
});
