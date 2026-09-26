<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('campaigns', 'auto_restart')) {
                $table->tinyInteger('auto_restart')->default(1)->after('status');
            }
            if (!Schema::hasColumn('campaigns', 'loop_count')) {
                $table->integer('loop_count')->default(0)->after('auto_restart');
            }
        });

        // Set all existing campaigns to auto-restart by default
        DB::table('campaigns')->update(['auto_restart' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('campaigns', 'auto_restart')) {
                $table->dropColumn('auto_restart');
            }
            if (Schema::hasColumn('campaigns', 'loop_count')) {
                $table->dropColumn('loop_count');
            }
        });
    }
};
