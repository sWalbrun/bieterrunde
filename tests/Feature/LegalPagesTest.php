<?php

it('serves the legal pages publicly', function (string $route, string $heading) {
    auth()->logout();

    $this->get($route)
        ->assertOk()
        ->assertSee($heading);
})->with([
    ['/impressum', 'Impressum'],
    ['/datenschutz', 'Datenschutzerklärung'],
]);

it('links the legal pages from the login page', function () {
    auth()->logout();

    $this->get('/login')
        ->assertSeeHtml(route('imprint'))
        ->assertSeeHtml(route('privacy'));
});
