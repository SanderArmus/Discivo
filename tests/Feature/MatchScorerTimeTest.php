<?php

use App\Models\Color;
use App\Models\Disc;
use App\Models\Location;
use App\Models\User;
use App\Services\MatchScorer;

test('time score uses week-based buckets', function () {
    $scorer = new MatchScorer;

    $color = Color::query()->create(['name' => 'Red']);

    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $lost = Disc::query()->create([
        'user_id' => $user->id,
        'status' => 'lost',
        'occurred_at' => now()->subDays(10),
        'condition_estimate' => 'good',
        'active' => true,
    ]);

    $found = Disc::query()->create([
        'user_id' => $otherUser->id,
        'status' => 'found',
        'occurred_at' => now(),
        'condition_estimate' => 'good',
        'active' => true,
    ]);

    $lost->colors()->attach($color->id);
    $found->colors()->attach($color->id);

    Location::query()->create([
        'disc_id' => $lost->id,
        'latitude' => 59.437,
        'longitude' => 24.7536,
        'location_type' => 'lost',
        'location_text' => 'Tallinn',
    ]);

    Location::query()->create([
        'disc_id' => $found->id,
        'latitude' => 59.437,
        'longitude' => 24.7536,
        'location_type' => 'found',
        'location_text' => 'Tallinn',
    ]);

    $scored = $scorer->score(
        $lost->load(['colors', 'locations']),
        $found->load(['colors', 'locations'])
    );

    expect($scored)->not->toBeNull();
    $gapDays = (float) $lost->occurred_at->diffInSeconds($found->occurred_at) / 86400;
    expect($gapDays)->toBeGreaterThan(7.0);
    expect($gapDays)->toBeLessThanOrEqual(14.0);
    expect($scored['time_score'])->toBe(80.0);
});

test('found before lost is discarded', function () {
    $scorer = new MatchScorer;

    $color = Color::query()->create(['name' => 'Blue']);

    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $lost = Disc::query()->create([
        'user_id' => $user->id,
        'status' => 'lost',
        'occurred_at' => now(),
        'condition_estimate' => 'good',
        'active' => true,
    ]);

    $found = Disc::query()->create([
        'user_id' => $otherUser->id,
        'status' => 'found',
        'occurred_at' => now()->subDay(),
        'condition_estimate' => 'good',
        'active' => true,
    ]);

    $lost->colors()->attach($color->id);
    $found->colors()->attach($color->id);

    Location::query()->create([
        'disc_id' => $lost->id,
        'latitude' => 59.437,
        'longitude' => 24.7536,
        'location_type' => 'lost',
        'location_text' => 'Tallinn',
    ]);

    Location::query()->create([
        'disc_id' => $found->id,
        'latitude' => 59.437,
        'longitude' => 24.7536,
        'location_type' => 'found',
        'location_text' => 'Tallinn',
    ]);

    $scored = $scorer->score($lost->load(['colors', 'locations']), $found->load(['colors', 'locations']));

    expect($scored)->toBeNull();
});
