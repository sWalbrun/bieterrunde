<?php

use App\Enums\EnumRole;
use App\Enums\EnumTenantRequestStatus;
use App\Filament\Resources\TenantRequestResource;
use App\Filament\Resources\TenantRequestResource\Pages\ListTenantRequests;
use App\Jobs\SetTenantCookie;
use App\Models\Tenant;
use App\Models\TenantRequest;
use App\Models\User;
use App\Notifications\TenantRequestRejected;
use App\Notifications\WelcomeTenantAdmin;
use Illuminate\Support\Facades\Notification;

use function Pest\Livewire\livewire;

it('denies plain admins access to the request resource', function () {
    /** @var Tenant $tenant */
    $tenant = Tenant::query()->create([Tenant::COL_ID => 'foo']);
    /** @var User $admin */
    $admin = User::factory()->admin()->create([User::COL_FK_TENANT => $tenant->id]);

    $this->actingAs($admin);
    $this->call('GET', TenantRequestResource::getUrl(), cookies: [SetTenantCookie::TENANT_ID => $tenant->id])
        ->assertForbidden();
});

it('approves a request, provisions the tenant and welcomes the admin', function () {
    Notification::fake();

    /** @var TenantRequest $request */
    $request = TenantRequest::factory()->create([
        TenantRequest::COL_SOLAWI_NAME => 'Solawi Sonnenacker',
        TenantRequest::COL_EMAIL => 'maria@solawi.test',
        TenantRequest::COL_NAME => 'Maria',
    ]);

    livewire(ListTenantRequests::class)
        // The identifier is proposed from the solawi name and stays editable
        ->mountTableAction('approve', $request)
        ->assertTableActionDataSet(['tenantId' => 'solawi-sonnenacker'])
        ->callMountedTableAction();

    $request->refresh();
    expect($request->status)->toBe(EnumTenantRequestStatus::APPROVED)
        ->and($request->tenant_id)->toBe('solawi-sonnenacker')
        ->and(Tenant::query()->where(Tenant::COL_ID, '=', 'solawi-sonnenacker')->exists())->toBeTrue();

    /** @var User $newAdmin */
    $newAdmin = User::query()->withoutGlobalScopes()->where(User::COL_EMAIL, '=', 'maria@solawi.test')->firstOrFail();
    expect($newAdmin->role)->toBe(EnumRole::ADMIN)
        ->and($newAdmin->tenant_id)->toBe('solawi-sonnenacker');

    Notification::assertSentTo($newAdmin, WelcomeTenantAdmin::class);
});

it('rejects an already taken tenant identifier but accepts a custom one', function () {
    Notification::fake();
    Tenant::query()->create([Tenant::COL_ID => 'solawi-sonnenacker']);

    /** @var TenantRequest $request */
    $request = TenantRequest::factory()->create([
        TenantRequest::COL_SOLAWI_NAME => 'Solawi Sonnenacker',
    ]);

    livewire(ListTenantRequests::class)
        ->callTableAction('approve', $request, data: ['tenantId' => 'solawi-sonnenacker'])
        ->assertHasTableActionErrors(['tenantId']);

    expect($request->refresh()->status)->toBe(EnumTenantRequestStatus::PENDING);

    livewire(ListTenantRequests::class)
        ->callTableAction('approve', $request, data: ['tenantId' => 'sonnenacker-neu'])
        ->assertHasNoTableActionErrors();

    expect($request->refresh()->tenant_id)->toBe('sonnenacker-neu');
});

it('refuses to approve when the email already belongs to a user', function () {
    Notification::fake();
    User::factory()->create([User::COL_EMAIL => 'taken@solawi.test']);

    /** @var TenantRequest $request */
    $request = TenantRequest::factory()->create([
        TenantRequest::COL_EMAIL => 'taken@solawi.test',
    ]);

    livewire(ListTenantRequests::class)
        ->callTableAction('approve', $request, data: ['tenantId' => 'irgendeine-solawi']);

    expect($request->refresh()->status)->toBe(EnumTenantRequestStatus::PENDING)
        ->and($request->tenant_id)->toBeNull();

    Notification::assertNothingSent();
});

it('rejects a request and informs the requester', function () {
    Notification::fake();

    /** @var TenantRequest $request */
    $request = TenantRequest::factory()->create([
        TenantRequest::COL_EMAIL => 'maria@solawi.test',
    ]);

    livewire(ListTenantRequests::class)
        ->callTableAction('reject', $request);

    expect($request->refresh()->status)->toBe(EnumTenantRequestStatus::REJECTED);

    Notification::assertSentOnDemand(
        TenantRequestRejected::class,
        fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'maria@solawi.test'
    );
});
