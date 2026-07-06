<?php

use App\Enums\EnumTenantRequestStatus;
use App\Livewire\RequestTestAccount;
use App\Models\TenantRequest;
use App\Models\User;
use App\Notifications\TenantRequestReceived;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

use function Pest\Livewire\livewire;

beforeEach(function () {
    auth()->logout();
    RateLimiter::clear('tenant-request:127.0.0.1');
    Notification::fake();
});

it('renders the public request page', function () {
    $this->get('/request-account')
        ->assertOk()
        ->assertSeeLivewire(RequestTestAccount::class);
});

it('stores a request and notifies the super admins', function () {
    /** @var User $superAdmin */
    $superAdmin = User::factory()->superAdmin()->create();

    livewire(RequestTestAccount::class)
        ->set('name', 'Maria')
        ->set('email', 'maria@solawi.test')
        ->set('solawiName', 'Solawi Sonnenacker')
        ->set('websiteUrl', 'https://sonnenacker.example')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    /** @var TenantRequest $request */
    $request = TenantRequest::query()->firstOrFail();
    expect($request->status)->toBe(EnumTenantRequestStatus::PENDING)
        ->and($request->solawiName)->toBe('Solawi Sonnenacker');

    Notification::assertSentTo($superAdmin, TenantRequestReceived::class);
});

it('validates the input', function () {
    livewire(RequestTestAccount::class)
        ->set('email', 'not-an-email')
        ->set('websiteUrl', 'not-a-url')
        ->call('submit')
        ->assertHasErrors(['name', 'email', 'solawiName', 'websiteUrl']);

    expect(TenantRequest::query()->count())->toBe(0);
});

it('silently swallows honeypot submissions', function () {
    livewire(RequestTestAccount::class)
        ->set('name', 'Bot')
        ->set('email', 'bot@spam.test')
        ->set('solawiName', 'Spamwi')
        ->set('website', 'https://spam.example')
        ->call('submit')
        ->assertSet('submitted', true);

    expect(TenantRequest::query()->count())->toBe(0);
    Notification::assertNothingSent();
});

it('does not create duplicates for known emails but shows the same success', function () {
    User::factory()->create([User::COL_EMAIL => 'member@solawi.test']);

    livewire(RequestTestAccount::class)
        ->set('name', 'Maria')
        ->set('email', 'member@solawi.test')
        ->set('solawiName', 'Solawi Sonnenacker')
        ->call('submit')
        ->assertSet('submitted', true);

    expect(TenantRequest::query()->count())->toBe(0);
    Notification::assertNothingSent();
});

it('rate limits the form', function () {
    for ($i = 0; $i < 3; $i++) {
        livewire(RequestTestAccount::class)
            ->set('name', "Maria $i")
            ->set('email', "maria$i@solawi.test")
            ->set('solawiName', "Solawi $i")
            ->call('submit')
            ->assertHasNoErrors();
    }

    livewire(RequestTestAccount::class)
        ->set('name', 'Maria 4')
        ->set('email', 'maria4@solawi.test')
        ->set('solawiName', 'Solawi 4')
        ->call('submit')
        ->assertHasErrors('email');

    expect(TenantRequest::query()->count())->toBe(3);
});

it('links the request page from the login page', function () {
    $this->get('/login')->assertSeeHtml(route('request-account'));
});
