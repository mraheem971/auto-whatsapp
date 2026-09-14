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
        Schema::table('device_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('device_messages', 'user_id')) {
                $table->unsignedBigInteger('user_id')->default(0)->after('id')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_messages', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
