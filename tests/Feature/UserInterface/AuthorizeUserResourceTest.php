<?php

use App\Filament\Resources\UserResource;
use App\Jobs\SetTenantCookie;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Permission;

it('denies without permission to access routes', function (string $route) {

    /** @var Tenant $tenant */
    $tenant = Tenant::query()->create([Tenant::COL_ID => 'foo']);

    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user);
    $this
        ->call('GET', $route, cookies: [SetTenantCookie::TENANT_ID => $tenant->id])
        ->assertForbidden();
})->with([
    ['/main/users'],
    ['/main/users/create'],
]);

it('allows to access the page', function () {
    /** @var Tenant $tenant */
    $tenant = Tenant::query()->create([Tenant::COL_ID => 'foo']);

    /** @var User $userToLogin */
    $userToLogin = User::factory()->create([User::COL_FK_TENANT => $tenant->id]);
    $userToLogin->assignRole(config('filament-shield.super_admin.name'));
    $userToLogin->givePermissionTo(Permission::create(['name' => 'view_any_user']));

    $this->actingAs($userToLogin);
    $this
        ->call('GET', UserResource::getUrl(), cookies: [SetTenantCookie::TENANT_ID => $tenant->id])
        ->assertSuccessful();
});
