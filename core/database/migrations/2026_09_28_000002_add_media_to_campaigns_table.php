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
        Schema::table('campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('campaigns', 'media_url')) {
                $table->text('media_url')->nullable()->after('message');
            }
            if (!Schema::hasColumn('campaigns', 'media_type')) {
                $table->string('media_type', 50)->default('text')->after('media_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('campaigns', 'media_type')) {
                $table->dropColumn('media_type');
            }
            if (Schema::hasColumn('campaigns', 'media_url')) {
                $table->dropColumn('media_url');
            }
        });
    }
};
