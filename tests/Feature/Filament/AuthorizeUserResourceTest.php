<?php

use App\Filament\Resources\UserResource;
use App\Jobs\SetTenantCookie;
use App\Models\Tenant;
use App\Models\User;

it('denies members access to the admin panel', function (string $route) {

    /** @var Tenant $tenant */
    $tenant = Tenant::query()->create([Tenant::COL_ID => 'foo']);

    /** @var User $user */
    $user = User::factory()->create([User::COL_FK_TENANT => $tenant->id]);

    $this->actingAs($user);
    $this
        ->call('GET', $route, cookies: [SetTenantCookie::TENANT_ID => $tenant->id])
        ->assertForbidden();
})->with([
    ['/main'],
    ['/main/users'],
    ['/main/users/create'],
]);

it('allows super admins to access the user resource', function () {
    /** @var Tenant $tenant */
    $tenant = Tenant::query()->create([Tenant::COL_ID => 'foo']);

    /** @var User $userToLogin */
    $userToLogin = User::factory()->superAdmin()->create([User::COL_FK_TENANT => $tenant->id]);

    $this->actingAs($userToLogin);
    $this
        ->call('GET', UserResource::getUrl(), cookies: [SetTenantCookie::TENANT_ID => $tenant->id])
        ->assertSuccessful();
});
