<?php

use App\Jobs\SetTenantCookie;
use App\Livewire\Dashboard;
use App\Models\Tenant;
use App\Models\User;

it('shows the dashboard to an authenticated member', function () {
    /** @var Tenant $tenant */
    $tenant = Tenant::query()->create([Tenant::COL_ID => 'foo']);
    /** @var User $user */
    $user = User::factory()->create([User::COL_FK_TENANT => $tenant->id]);
    $this->actingAs($user);

    $this->call('GET', '/', cookies: [SetTenantCookie::TENANT_ID => $tenant->id])
        ->assertOk()
        ->assertSeeLivewire(Dashboard::class)
        ->assertSee($user->name);
});

it('redirects guests to the login page', function () {
    auth()->logout();
    Tenant::query()->create([Tenant::COL_ID => 'foo']);

    $this->call('GET', '/', cookies: [SetTenantCookie::TENANT_ID => 'foo'])
        ->assertRedirect(route('login'));
});
