<?php

use App\Models\User;

test('banned user is redirected to banned page', function () {
    $user = User::factory()->create([
        'banned_at' => now(),
        'banned_reason' => 'Testing',
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect('/banned');

    $this->actingAs($user)
        ->get('/banned')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Banned'));
});
