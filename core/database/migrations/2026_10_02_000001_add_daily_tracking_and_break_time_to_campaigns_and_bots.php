<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Auto Replies: Daily hits tracking
        Schema::table('auto_replies', function (Blueprint $table) {
            if (!Schema::hasColumn('auto_replies', 'daily_hit_count')) {
                $table->unsignedInteger('daily_hit_count')->default(0)->after('hit_count');
            }
            if (!Schema::hasColumn('auto_replies', 'daily_hit_date')) {
                $table->date('daily_hit_date')->nullable()->default(null)->after('daily_hit_count');
            }
        });

        // 2. Campaigns: Break Time / Sleep Mode scheduling
        Schema::table('campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('campaigns', 'sleep_mode')) {
                $table->boolean('sleep_mode')->default(0)->after('reset_after_count');
            }
            if (!Schema::hasColumn('campaigns', 'sleep_start_time')) {
                $table->string('sleep_start_time', 10)->default('22:00')->after('sleep_mode');
            }
            if (!Schema::hasColumn('campaigns', 'sleep_end_time')) {
                $table->string('sleep_end_time', 10)->default('08:00')->after('sleep_start_time');
            }
        });

        // 3. User Bot Settings: Ensure sleep mode columns exist
        Schema::table('user_bot_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('user_bot_settings', 'sleep_mode')) {
                $table->boolean('sleep_mode')->default(0);
            }
            if (!Schema::hasColumn('user_bot_settings', 'sleep_start_time')) {
                $table->string('sleep_start_time', 10)->default('22:00');
            }
            if (!Schema::hasColumn('user_bot_settings', 'sleep_end_time')) {
                $table->string('sleep_end_time', 10)->default('08:00');
            }
        });
    }

    public function down(): void
    {
        Schema::table('auto_replies', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('auto_replies', 'daily_hit_count')) $cols[] = 'daily_hit_count';
            if (Schema::hasColumn('auto_replies', 'daily_hit_date')) $cols[] = 'daily_hit_date';
            if (!empty($cols)) $table->dropColumn($cols);
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('campaigns', 'sleep_mode')) $cols[] = 'sleep_mode';
            if (Schema::hasColumn('campaigns', 'sleep_start_time')) $cols[] = 'sleep_start_time';
            if (Schema::hasColumn('campaigns', 'sleep_end_time')) $cols[] = 'sleep_end_time';
            if (!empty($cols)) $table->dropColumn($cols);
        });

        Schema::table('user_bot_settings', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('user_bot_settings', 'sleep_mode')) $cols[] = 'sleep_mode';
            if (Schema::hasColumn('user_bot_settings', 'sleep_start_time')) $cols[] = 'sleep_start_time';
            if (Schema::hasColumn('user_bot_settings', 'sleep_end_time')) $cols[] = 'sleep_end_time';
            if (!empty($cols)) $table->dropColumn($cols);
        });
    }
};
