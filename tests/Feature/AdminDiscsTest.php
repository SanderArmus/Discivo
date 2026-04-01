<?php

use App\Models\Disc;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('non-admin users cannot access admin discs', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.discs.index'))
        ->assertForbidden();
});

test('admin users can view all discs', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();

    $owner = User::factory()->create();
    $disc = Disc::create([
        'user_id' => $owner->id,
        'status' => 'lost',
        'model_name' => 'Destroyer',
        'plastic_type' => 'Star',
        'condition_estimate' => 'good',
        'active' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.discs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Discs')
            ->where('discs.data.0.id', $disc->id));
});

test('admin can update disc status', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();

    $owner = User::factory()->create();
    $disc = Disc::create([
        'user_id' => $owner->id,
        'status' => 'lost',
        'model_name' => 'Destroyer',
        'condition_estimate' => 'good',
        'active' => true,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.discs.update', ['disc' => $disc->id]), [
            'status' => 'found',
        ])
        ->assertRedirect();

    expect($disc->fresh()->status)->toBe('found');
});
