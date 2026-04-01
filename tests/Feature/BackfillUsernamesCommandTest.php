<?php

use App\Models\User;

test('backfill usernames command sets unique usernames for missing users', function () {
    $a = User::factory()->create([
        'name' => 'John Doe',
        'username' => null,
        'email' => 'a@example.com',
    ]);

    $b = User::factory()->create([
        'name' => 'John Doe',
        'username' => '',
        'email' => 'b@example.com',
    ]);

    User::factory()->create([
        'name' => 'Existing',
        'username' => 'john-doe',
        'email' => 'c@example.com',
    ]);

    $this->artisan('users:backfill-usernames')->assertExitCode(0);

    $a->refresh();
    $b->refresh();

    expect($a->username)->not->toBeNull()->not->toBe('');
    expect($b->username)->not->toBeNull()->not->toBe('');
    expect($a->username)->not->toBe($b->username);
});
