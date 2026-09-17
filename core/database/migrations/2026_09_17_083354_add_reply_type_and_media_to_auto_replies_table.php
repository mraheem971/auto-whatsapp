<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('auto_replies', function (Blueprint $table) {
            if (!Schema::hasColumn('auto_replies', 'reply_type')) {
                $table->string('reply_type', 50)->default('text')->after('match_type');
            }
            if (!Schema::hasColumn('auto_replies', 'media_url')) {
                $table->text('media_url')->nullable()->after('reply_message');
            }
            if (!Schema::hasColumn('auto_replies', 'media_path')) {
                $table->text('media_path')->nullable()->after('media_url');
            }
            if (!Schema::hasColumn('auto_replies', 'cooldown_minutes')) {
                $table->integer('cooldown_minutes')->default(0)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_replies', function (Blueprint $table) {
            if (Schema::hasColumn('auto_replies', 'reply_type')) {
                $table->dropColumn('reply_type');
            }
            if (Schema::hasColumn('auto_replies', 'media_url')) {
                $table->dropColumn('media_url');
            }
            if (Schema::hasColumn('auto_replies', 'media_path')) {
                $table->dropColumn('media_path');
            }
            if (Schema::hasColumn('auto_replies', 'cooldown_minutes')) {
                $table->dropColumn('cooldown_minutes');
            }
        });
    }
};
