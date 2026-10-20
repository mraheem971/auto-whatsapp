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
        if (Schema::hasTable('message_templates') && !Schema::hasColumn('message_templates', 'media_url')) {
            Schema::table('message_templates', function (Blueprint $table) {
                $table->string('media_url', 2048)->nullable()->after('message');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('message_templates') && Schema::hasColumn('message_templates', 'media_url')) {
            Schema::table('message_templates', function (Blueprint $table) {
                $table->dropColumn('media_url');
            });
        }
    }
};
