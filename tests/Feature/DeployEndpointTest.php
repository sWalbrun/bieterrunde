<?php

use Illuminate\Support\Facades\Artisan;

beforeEach(fn () => auth()->logout());

it('is disabled when no deploy token is configured', function () {
    config(['deploy.token' => '']);

    $this->get('/__deploy/anything')->assertNotFound();
});

it('rejects a wrong token', function () {
    config(['deploy.token' => 'the-real-secret']);

    $this->get('/__deploy/wrong')->assertNotFound();
});

it('runs the whitelisted deploy tasks with the correct token', function () {
    config(['deploy.token' => 'the-real-secret']);

    // Don't actually migrate/cache during the test run — just assert wiring.
    Artisan::shouldReceive('call')->andReturn(0);
    Artisan::shouldReceive('output')->andReturn('ok');

    $this->get('/__deploy/the-real-secret')
        ->assertOk()
        ->assertSee('php artisan migrate')
        ->assertSee('Done.');
});
