<?php

use App\Models\Disc;
use App\Models\User;
use Illuminate\Support\Carbon;

test('discs:expire marks expired discs inactive', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-20 12:00:00'));

    $user = User::factory()->create();

    $disc = Disc::create([
        'user_id' => $user->id,
        'status' => 'lost',
        'active' => true,
        'expires_at' => now()->subMinute(),
    ]);

    $this->artisan('discs:expire')->assertExitCode(0);

    expect($disc->refresh()->active)->toBeFalse();
});

test('owner can renew an inactive disc for 90 days', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-20 12:00:00'));

    $user = User::factory()->create();
    $other = User::factory()->create();

    $disc = Disc::create([
        'user_id' => $user->id,
        'status' => 'found',
        'active' => false,
        'expires_at' => now()->subDay(),
    ]);

    $this->actingAs($other)
        ->post("/discs/{$disc->id}/renew")
        ->assertForbidden();

    $this->actingAs($user)
        ->post("/discs/{$disc->id}/renew")
        ->assertRedirect();

    $disc->refresh();
    expect($disc->active)->toBeTrue();
    expect($disc->expires_at?->format('Y-m-d H:i:s'))->toBe(now()->addDays(90)->format('Y-m-d H:i:s'));
});
