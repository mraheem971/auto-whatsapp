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
        Schema::table('user_bot_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('user_bot_settings', 'default_whatsapp_account_id')) {
                $table->unsignedBigInteger('default_whatsapp_account_id')->default(0)->after('user_id');
            }
            if (!Schema::hasColumn('user_bot_settings', 'webhook_url')) {
                $table->string('webhook_url')->nullable()->after('sleep_end_time');
            }
            if (!Schema::hasColumn('user_bot_settings', 'webhook_secret')) {
                $table->string('webhook_secret', 64)->nullable()->after('webhook_url');
            }
            if (!Schema::hasColumn('user_bot_settings', 'auto_fallback')) {
                $table->boolean('auto_fallback')->default(true)->after('webhook_secret');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_bot_settings', function (Blueprint $table) {
            $table->dropColumn(['default_whatsapp_account_id', 'webhook_url', 'webhook_secret', 'auto_fallback']);
        });
    }
};
