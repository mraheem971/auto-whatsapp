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
        if (!Schema::hasTable('whatsapp_accounts')) {
            Schema::create('whatsapp_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_id')->default(0);
                $table->unsignedBigInteger('user_id')->default(0);
                $table->string('session_id', 100)->unique();
                $table->string('account_name', 150);
                $table->string('phone_number', 50)->nullable();
                $table->string('jid', 150)->nullable();
                $table->string('profile_name', 150)->nullable();
                $table->string('avatar_url', 255)->nullable();
                $table->tinyInteger('status')->default(0)->comment('1=active/connected, 0=pending/disconnected');
                $table->text('connection_data')->nullable();
                $table->timestamp('last_connected_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_accounts');
    }
};
