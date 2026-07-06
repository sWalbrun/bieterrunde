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

it('queues the tenant cookie when switching via the table action', function () {
    /** @var Tenant $other */
    $other = Tenant::query()->create([Tenant::COL_ID => 'other']);

    livewire(ListTenants::class)
        ->callTableAction('switch', $other);

    $queued = Cookie::queued(SetTenantCookie::TENANT_ID);
    expect($queued)->not->toBeNull()
        ->and($queued->getValue())->toBe('other');
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
