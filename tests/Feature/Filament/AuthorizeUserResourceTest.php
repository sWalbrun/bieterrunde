<?php

use App\Filament\Resources\UserResource;
use App\Jobs\SetTenantCookie;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
    // The spatie role and permission are still required for the resource policies until shield is removed
    $userToLogin->assignRole(Role::findOrCreate(config('filament-shield.super_admin.name')));
    $userToLogin->givePermissionTo(Permission::create(['name' => 'view_any_user']));

    $this->actingAs($userToLogin);
    $this
        ->call('GET', UserResource::getUrl(), cookies: [SetTenantCookie::TENANT_ID => $tenant->id])
        ->assertSuccessful();
});
