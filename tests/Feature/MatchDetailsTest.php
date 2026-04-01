<?php

use App\Models\Color;
use App\Models\Disc;
use App\Models\Location;
use App\Models\MatchThread;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('match details page can be viewed by participants', function () {
    $lostOwner = User::factory()->create(['name' => 'Lost Owner']);
    $foundOwner = User::factory()->create(['name' => 'Found Owner']);

    $blue = Color::create(['name' => 'Blue']);

    $lostDisc = Disc::create([
        'user_id' => $lostOwner->id,
        'status' => 'lost',
        'occurred_at' => now()->subDay(),
        'manufacturer' => 'Innova',
        'model_name' => 'Glitch',
        'plastic_type' => 'Neutron',
        'back_text' => 'ABC',
        'condition_estimate' => 'good',
        'active' => true,
    ]);
    $lostDisc->colors()->attach($blue->id);
    Location::create([
        'disc_id' => $lostDisc->id,
        'latitude' => 59.437,
        'longitude' => 24.7536,
        'location_type' => 'lost',
        'location_text' => 'Lost Park',
    ]);

    $foundDisc = Disc::create([
        'user_id' => $foundOwner->id,
        'status' => 'found',
        'occurred_at' => now(),
        'manufacturer' => 'Innova',
        'model_name' => 'Glitch',
        'plastic_type' => 'Neutron',
        'back_text' => 'ABC',
        'condition_estimate' => 'good',
        'active' => true,
    ]);
    $foundDisc->colors()->attach($blue->id);
    Location::create([
        'disc_id' => $foundDisc->id,
        'latitude' => 59.44,
        'longitude' => 24.75,
        'location_type' => 'found',
        'location_text' => 'Found Park',
    ]);

    $thread = MatchThread::create([
        'lost_disc_id' => $lostDisc->id,
        'found_disc_id' => $foundDisc->id,
        'match_score' => 88.0,
        'status' => 'pending',
    ]);

    $this->actingAs($lostOwner)
        ->get(route('matches.details', ['match' => $thread->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('MatchDetails')
            ->where('match.id', $thread->id)
            ->where('lostDisc.id', $lostDisc->id)
            ->where('foundDisc.id', $foundDisc->id));
});

test('match details page is forbidden for non-participants', function () {
    $lostOwner = User::factory()->create();
    $foundOwner = User::factory()->create();
    $other = User::factory()->create();

    $lostDisc = Disc::create([
        'user_id' => $lostOwner->id,
        'status' => 'lost',
        'active' => true,
    ]);

    $foundDisc = Disc::create([
        'user_id' => $foundOwner->id,
        'status' => 'found',
        'active' => true,
    ]);

    $thread = MatchThread::create([
        'lost_disc_id' => $lostDisc->id,
        'found_disc_id' => $foundDisc->id,
        'match_score' => 80.0,
        'status' => 'pending',
    ]);

    $this->actingAs($other)
        ->get(route('matches.details', ['match' => $thread->id]))
        ->assertForbidden();
});
