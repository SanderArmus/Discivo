<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\Confirmation;
use App\Models\Disc;
use App\Models\Location;
use App\Models\MatchThread;
use App\Models\Message;
use App\Models\User;
use App\Services\MatchScorer;
use Illuminate\Database\Seeder;

class LocalDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $scorer = new MatchScorer;

        // Create a small shared color palette.
        $red = Color::query()->firstOrCreate(['name' => 'Red']);
        $blue = Color::query()->firstOrCreate(['name' => 'Blue']);
        $white = Color::query()->firstOrCreate(['name' => 'White']);
        $black = Color::query()->firstOrCreate(['name' => 'Black']);

        // Demo users.
        $admin = User::query()->where('email', 'test@test.ee')->first();
        if ($admin === null) {
            // Fallback (in case DatabaseSeeder wasn't run for some reason).
            $admin = User::query()->create([
                'username' => 'test',
                'name' => 'Test Kasutaja',
                'email' => 'test@test.ee',
                'password' => 'TestKasutaja123',
                'email_verified_at' => now(),
                'role' => 'admin',
            ]);
        }

        $finder = User::query()->firstOrCreate(
            ['email' => 'finder@test.ee'],
            [
                'username' => 'finder',
                'name' => 'Leidja',
                'password' => 'TestKasutaja123',
                'email_verified_at' => now(),
                'role' => null,
            ]
        );

        $other = User::query()->firstOrCreate(
            ['email' => 'player@test.ee'],
            [
                'username' => 'player',
                'name' => 'Mängija',
                'password' => 'TestKasutaja123',
                'email_verified_at' => now(),
                'role' => null,
            ]
        );

        // Demo discs (lost + found) with enough shared info to produce a match.
        $lost = Disc::query()->firstOrCreate(
            [
                'user_id' => $admin->id,
                'status' => 'lost',
                'back_text' => 'SA',
            ],
            [
                'occurred_at' => now()->subDays(10),
                'manufacturer' => 'Innova',
                'model_name' => 'Destroyer',
                'plastic_type' => 'Star',
                'condition_estimate' => 'good',
                'active' => true,
            ]
        );

        $found = Disc::query()->firstOrCreate(
            [
                'user_id' => $finder->id,
                'status' => 'found',
                'back_text' => 'SA',
            ],
            [
                'occurred_at' => now()->subDays(2),
                'manufacturer' => 'Innova',
                'model_name' => 'Destroyer',
                'plastic_type' => 'Star',
                'condition_estimate' => 'good',
                'active' => true,
            ]
        );

        $lost->colors()->syncWithoutDetaching([$red->id, $white->id]);
        $found->colors()->syncWithoutDetaching([$red->id]);

        Location::query()->firstOrCreate(
            ['disc_id' => $lost->id],
            [
                'latitude' => 59.437,
                'longitude' => 24.7536,
                'location_type' => 'lost',
                'location_text' => 'Tallinn',
            ]
        );

        Location::query()->firstOrCreate(
            ['disc_id' => $found->id],
            [
                'latitude' => 59.437,
                'longitude' => 24.7536,
                'location_type' => 'found',
                'location_text' => 'Tallinn',
            ]
        );

        $lost->load(['colors', 'locations']);
        $found->load(['colors', 'locations']);

        $scored = $scorer->score($lost, $found);

        if ($scored !== null) {
            $thread = MatchThread::query()->firstOrCreate(
                [
                    'lost_disc_id' => $lost->id,
                    'found_disc_id' => $found->id,
                ],
                [
                    'match_score' => $scored['match_score'],
                    'status' => 'pending',
                ]
            );

            Confirmation::query()->firstOrCreate(
                ['match_id' => $thread->id],
                [
                    'owner_confirmed' => false,
                    'finder_confirmed' => false,
                ]
            );

            Message::query()->firstOrCreate(
                [
                    'match_id' => $thread->id,
                    'sender_id' => $finder->id,
                    'receiver_id' => $admin->id,
                ],
                [
                    'content' => 'Tere! Leidsin ketta, kas “SA” on sinu märgistus?',
                ]
            );

            Message::query()->firstOrCreate(
                [
                    'match_id' => $thread->id,
                    'sender_id' => $admin->id,
                    'receiver_id' => $finder->id,
                ],
                [
                    'content' => 'Jah, see on minu ketas. Aitäh! Kus sa leidsid?',
                ]
            );
        }

        // Extra discs to show that not everything matches.
        $otherLost = Disc::query()->firstOrCreate(
            [
                'user_id' => $other->id,
                'status' => 'lost',
                'back_text' => '—',
            ],
            [
                'occurred_at' => now()->subDays(3),
                'manufacturer' => 'Discraft',
                'model_name' => 'Buzzz',
                'plastic_type' => 'ESP',
                'condition_estimate' => 'fair',
                'active' => true,
            ]
        );

        $otherLost->colors()->syncWithoutDetaching([$blue->id, $black->id]);
    }
}

