<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_bot_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('user_bot_settings', 'delay_after_count')) {
                $table->unsignedInteger('delay_after_count')->default(50)->after('max_delay_seconds');
            }
            if (!Schema::hasColumn('user_bot_settings', 'delay_after_duration')) {
                $table->unsignedInteger('delay_after_duration')->default(5)->after('delay_after_count');
            }
            if (!Schema::hasColumn('user_bot_settings', 'reset_after_count')) {
                $table->unsignedInteger('reset_after_count')->default(100)->after('delay_after_duration');
            }
        });

        Schema::table('campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('campaigns', 'delay_after_count')) {
                $table->unsignedInteger('delay_after_count')->nullable()->default(50)->after('max_delay');
            }
            if (!Schema::hasColumn('campaigns', 'delay_after_duration')) {
                $table->unsignedInteger('delay_after_duration')->nullable()->default(5)->after('delay_after_count');
            }
            if (!Schema::hasColumn('campaigns', 'reset_after_count')) {
                $table->unsignedInteger('reset_after_count')->nullable()->default(100)->after('delay_after_duration');
            }
            if (!Schema::hasColumn('campaigns', 'batch_sent_count')) {
                $table->unsignedInteger('batch_sent_count')->default(0)->after('reset_after_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_bot_settings', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('user_bot_settings', 'delay_after_count')) $cols[] = 'delay_after_count';
            if (Schema::hasColumn('user_bot_settings', 'delay_after_duration')) $cols[] = 'delay_after_duration';
            if (Schema::hasColumn('user_bot_settings', 'reset_after_count')) $cols[] = 'reset_after_count';
            if (!empty($cols)) $table->dropColumn($cols);
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('campaigns', 'delay_after_count')) $cols[] = 'delay_after_count';
            if (Schema::hasColumn('campaigns', 'delay_after_duration')) $cols[] = 'delay_after_duration';
            if (Schema::hasColumn('campaigns', 'reset_after_count')) $cols[] = 'reset_after_count';
            if (!Schema::hasColumn('campaigns', 'batch_sent_count')) $cols[] = 'batch_sent_count';
            if (!empty($cols)) $table->dropColumn($cols);
        });
    }
};
