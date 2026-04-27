<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $users = DB::table('users')->whereNull('username')->get();
        $used = DB::table('users')->whereNotNull('username')->pluck('username')->flip()->all();

        foreach ($users as $user) {
            $base = Str::slug($user->name ?: 'user');
            $username = $base;
            $n = 0;
            while (isset($used[$username])) {
                $n++;
                $username = $base.$n;
            }
            $used[$username] = true;
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY username VARCHAR(255) NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN username SET NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY username VARCHAR(255) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN username DROP NOT NULL');
        }

        Schema::table('users', function (Blueprint $table) {});
    }
};
