<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

it('returns the avatar URL from GitHub', function () {
    Http::fake([
        'api.github.com/users/octocat' => Http::response([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/583231?v=4',
        ]),
    ]);

    $this->getJson(route('perfil.avatar', 'octocat'))
        ->assertOk()
        ->assertJson([
            'avatar' => 'https://avatars.githubusercontent.com/u/583231?v=4',
        ]);
});

it('returns the ui avatars fallback when GitHub has no matching user', function () {
    Http::fake([
        'api.github.com/users/unknown-user' => Http::response([], 404),
    ]);

    $this->getJson(route('perfil.avatar', 'unknown-user'))
        ->assertOk()
        ->assertJson([
            'avatar' => 'https://ui-avatars.com/api/?name=unknown-user',
        ]);
});

it('returns the ui avatars fallback when GitHub times out', function () {
    Http::fake([
        'api.github.com/users/octocat' => function () {
            throw new ConnectionException('Request timed out.');
        },
    ]);

    $this->getJson(route('perfil.avatar', 'octocat'))
        ->assertOk()
        ->assertJson([
            'avatar' => 'https://ui-avatars.com/api/?name=octocat',
        ]);
});
