<?php

use App\Http\Middleware\EnsureCanonicalHost;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

it('redirects to canonical host when host mismatches', function () {
    config()->set('app.url', 'http://localhost:8000');

    $path = '/'.Str::random(12);

    $request = Request::create('http://127.0.0.1'.$path.'?x=1', 'GET');

    $response = (new EnsureCanonicalHost)->handle($request, function (): Response {
        return new Response('ok');
    });

    expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
    expect($response->headers->get('Location'))->toBe('http://localhost:8000'.$path.'?x=1');
});

it('does not redirect when already on canonical host', function () {
    config()->set('app.url', 'http://localhost:8000');

    $path = '/'.Str::random(12);

    $request = Request::create('http://localhost:8000'.$path.'?x=1', 'GET');

    $response = (new EnsureCanonicalHost)->handle($request, function (): Response {
        return new Response('ok');
    });

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->getContent())->toBe('ok');
});
