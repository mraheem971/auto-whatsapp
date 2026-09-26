<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('campaigns', 'daily_limit')) {
                $table->unsignedInteger('daily_limit')->nullable()->default(null)->after('delay_seconds');
            }
            if (!Schema::hasColumn('campaigns', 'daily_sent_count')) {
                $table->unsignedInteger('daily_sent_count')->default(0)->after('daily_limit');
            }
            if (!Schema::hasColumn('campaigns', 'daily_sent_date')) {
                $table->date('daily_sent_date')->nullable()->default(null)->after('daily_sent_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('campaigns', 'daily_limit')) {
                $columns[] = 'daily_limit';
            }
            if (Schema::hasColumn('campaigns', 'daily_sent_count')) {
                $columns[] = 'daily_sent_count';
            }
            if (Schema::hasColumn('campaigns', 'daily_sent_date')) {
                $columns[] = 'daily_sent_date';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
