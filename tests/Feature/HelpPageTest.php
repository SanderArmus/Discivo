<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('help page is available to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('help'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Help'));
});
