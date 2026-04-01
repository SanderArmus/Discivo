<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackfillUsernamesCommand extends Command
{
    protected $signature = 'users:backfill-usernames {--dry-run : Show what would change without saving}';

    protected $description = 'Backfill missing usernames for existing users';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $users = User::query()
            ->whereNull('username')
            ->orWhere('username', '')
            ->orderBy('id')
            ->get(['id', 'name', 'username']);

        if ($users->isEmpty()) {
            $this->info('No users missing usernames.');

            return self::SUCCESS;
        }

        $existing = User::query()
            ->whereNotNull('username')
            ->where('username', '!=', '')
            ->pluck('username')
            ->map(fn ($v) => (string) $v)
            ->flip()
            ->all();

        foreach ($users as $user) {
            $base = Str::slug($user->name ?: 'user');
            $base = $base !== '' ? $base : 'user';

            // Keep room for suffixes while honoring max:30.
            $base = Str::limit($base, 24, '');

            $candidate = $base;
            $n = 0;

            while (isset($existing[$candidate]) || $candidate === '') {
                $n++;
                $suffix = (string) $n;
                $candidate = Str::limit($base, 30 - strlen($suffix), '').$suffix;
            }

            $existing[$candidate] = true;

            $this->line("{$user->id}: {$user->name} => {$candidate}");

            if (! $dryRun) {
                $user->username = $candidate;
                $user->save();
            }
        }

        $this->info($dryRun ? 'Dry run complete.' : 'Backfill complete.');

        return self::SUCCESS;
    }
}
