<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('discs', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('active');
        });

        DB::table('discs')
            ->select(['id', 'created_at'])
            ->whereNull('expires_at')
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    if (! isset($row->created_at)) {
                        continue;
                    }

                    DB::table('discs')
                        ->where('id', '=', $row->id)
                        ->update([
                            'expires_at' => Carbon::parse((string) $row->created_at)->addDays(90),
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discs', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
