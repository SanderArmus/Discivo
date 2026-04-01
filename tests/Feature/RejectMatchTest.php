<?php

use App\Models\Color;
use App\Models\Disc;
use App\Models\Location;
use App\Models\MatchThread;
use App\Models\User;
use App\Services\MatchFinder;

test('participant can reject a pending match and it no longer appears in potential matches', function () {
    $lostOwner = User::factory()->create();
    $finderUser = User::factory()->create();

    $blue = Color::create(['name' => 'Blue']);

    $lost = Disc::create([
        'user_id' => $lostOwner->id,
        'status' => 'lost',
        'occurred_at' => now()->subDay(),
        'condition_estimate' => 'good',
        'active' => true,
    ]);
    $lost->colors()->attach($blue->id);
    Location::create([
        'disc_id' => $lost->id,
        'latitude' => 59.437,
        'longitude' => 24.7536,
        'location_type' => 'lost',
    ]);

    $found = Disc::create([
        'user_id' => $finderUser->id,
        'status' => 'found',
        'occurred_at' => now(),
        'condition_estimate' => 'good',
        'active' => true,
    ]);
    $found->colors()->attach($blue->id);
    Location::create([
        'disc_id' => $found->id,
        'latitude' => 59.437,
        'longitude' => 24.7536,
        'location_type' => 'found',
    ]);

    $finder = new MatchFinder;
    $matches = $finder->findForUser($lostOwner, limit: 10, minScore: 60.0);
    expect($matches)->toHaveCount(1);

    $matchId = $matches[0]['id'];

    $this->actingAs($lostOwner)
        ->post(route('matches.reject', ['match' => $matchId]))
        ->assertRedirect(route('dashboard'));

    $thread = MatchThread::find($matchId);
    expect($thread)->not->toBeNull();
    expect($thread?->status)->toBe('rejected');

    $matchesAfter = $finder->findForUser($lostOwner, limit: 10, minScore: 60.0);
    expect($matchesAfter)->toHaveCount(0);
});

test('non-participant cannot reject a match', function () {
    $lostOwner = User::factory()->create();
    $finderUser = User::factory()->create();
    $other = User::factory()->create();

    $lost = Disc::create([
        'user_id' => $lostOwner->id,
        'status' => 'lost',
        'active' => true,
    ]);

    $found = Disc::create([
        'user_id' => $finderUser->id,
        'status' => 'found',
        'active' => true,
    ]);

    $thread = MatchThread::create([
        'lost_disc_id' => $lost->id,
        'found_disc_id' => $found->id,
        'match_score' => 80.0,
        'status' => 'pending',
    ]);

    $this->actingAs($other)
        ->post(route('matches.reject', ['match' => $thread->id]))
        ->assertForbidden();
});
