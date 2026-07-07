<?php

use App\Jobs\SetTenantCookie;
use App\Livewire\Auth\Login;
use App\Mail\LoginLinkMail;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

use function Pest\Livewire\livewire;

beforeEach(function () {
    auth()->logout();
    RateLimiter::clear('magic-link:member@solawi.test|127.0.0.1');
});

it('renders the login page', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSeeLivewire(Login::class);
});

it('redirects authenticated users away from the login page', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get('/login')->assertRedirect(url('/'));
});

it('sends a login link for a known email address', function () {
    Mail::fake();

    /** @var User $user */
    $user = User::factory()->create([User::COL_EMAIL => 'member@solawi.test']);

    livewire(Login::class)
        ->set('email', 'member@solawi.test')
        ->call('sendLink')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    Mail::assertSent(LoginLinkMail::class, fn (LoginLinkMail $mail) => $mail->user->is($user));
});

it('shows the same message for an unknown email address without sending mail', function () {
    Mail::fake();

    livewire(Login::class)
        ->set('email', 'unknown@solawi.test')
        ->call('sendLink')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    Mail::assertNothingSent();
});

it('rate limits login link requests', function () {
    Mail::fake();

    User::factory()->create([User::COL_EMAIL => 'member@solawi.test']);

    for ($i = 0; $i < 3; $i++) {
        livewire(Login::class)
            ->set('email', 'member@solawi.test')
            ->call('sendLink')
            ->assertHasNoErrors();
    }

    livewire(Login::class)
        ->set('email', 'member@solawi.test')
        ->call('sendLink')
        ->assertHasErrors('email');

    Mail::assertSent(LoginLinkMail::class, 3);
});

it('authenticates a member via signed link and redirects to the user area', function () {
    /** @var Tenant $tenant */
    $tenant = Tenant::query()->create([Tenant::COL_ID => 'foo']);
    /** @var User $user */
    $user = User::factory()->unverified()->create([User::COL_FK_TENANT => $tenant->id]);

    $url = URL::temporarySignedRoute('login.magic-link', now()->addMinutes(30), ['user' => $user->id]);

    $this->get($url)->assertRedirect(url('/'));

    expect(auth()->id())->toBe($user->id)
        ->and($user->refresh()->hasVerifiedEmail())->toBeTrue();
});

it('establishes a fresh tenant cookie on login even with a stale one present', function () {
    /** @var Tenant $tenant */
    $tenant = Tenant::query()->create([Tenant::COL_ID => 'foo']);
    /** @var User $user */
    $user = User::factory()->create([User::COL_FK_TENANT => $tenant->id]);

    $url = URL::temporarySignedRoute('login.magic-link', now()->addMinutes(30), ['user' => $user->id]);

    // A cookie pointing at a since-deleted tenant must not lock the user out
    $this->call('GET', $url, cookies: [SetTenantCookie::TENANT_ID => 'deleted-tenant'])
        ->assertRedirect(url('/'))
        ->assertCookie(SetTenantCookie::TENANT_ID, 'foo', encrypted: false);

    expect(auth()->id())->toBe($user->id);
});

it('authenticates an admin via signed link and redirects to the panel', function () {
    /** @var Tenant $tenant */
    $tenant = Tenant::query()->create([Tenant::COL_ID => 'foo']);
    /** @var User $user */
    $user = User::factory()->admin()->create([User::COL_FK_TENANT => $tenant->id]);

    $url = URL::temporarySignedRoute('login.magic-link', now()->addMinutes(30), ['user' => $user->id]);

    $this->get($url)->assertRedirect(Filament::getUrl());

    expect(auth()->id())->toBe($user->id);
});

it('rejects a tampered link', function () {
    /** @var User $user */
    $user = User::factory()->create();
    /** @var User $victim */
    $victim = User::factory()->create();

    $url = URL::temporarySignedRoute('login.magic-link', now()->addMinutes(30), ['user' => $user->id]);
    $tampered = str_replace("link/$user->id", "link/$victim->id", $url);

    $this->get($tampered)->assertForbidden();
    expect(auth()->check())->toBeFalse();
});

it('rejects an expired link', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $url = URL::temporarySignedRoute('login.magic-link', now()->addMinutes(30), ['user' => $user->id]);

    $this->travel(31)->minutes();

    $this->get($url)->assertForbidden();
    expect(auth()->check())->toBeFalse();
});

it('logs the user out and clears the tenant cookie', function () {
    /** @var Tenant $tenant */
    $tenant = Tenant::query()->create([Tenant::COL_ID => 'foo']);
    /** @var User $user */
    $user = User::factory()->create([User::COL_FK_TENANT => $tenant->id]);
    $this->actingAs($user);

    $response = $this->call('POST', '/logout', cookies: [SetTenantCookie::TENANT_ID => $tenant->id]);
    $response->assertRedirect(route('login'));

    // The tenant cookie is unencrypted, so we inspect the header directly
    $cookie = collect($response->headers->getCookies())
        ->first(fn ($cookie) => $cookie->getName() === SetTenantCookie::TENANT_ID);
    expect($cookie)->not->toBeNull()
        ->and($cookie->getExpiresTime())->toBeLessThan(now()->timestamp)
        ->and(auth()->check())->toBeFalse();
});
