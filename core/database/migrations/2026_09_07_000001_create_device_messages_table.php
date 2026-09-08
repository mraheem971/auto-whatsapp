<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('device_messages')) {
            Schema::create('device_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_id')->nullable();
                $table->unsignedBigInteger('whatsapp_account_id')->nullable();
                $table->string('session_id', 100)->nullable();
                $table->string('device_name', 100)->nullable();
                $table->string('sender_phone', 50)->nullable();
                $table->string('receiver', 100);
                $table->string('receiver_name', 100)->nullable();
                $table->string('target_jid', 100)->nullable();
                $table->boolean('is_group')->default(false);
                $table->string('group_name', 150)->nullable();
                $table->text('message')->nullable();
                $table->text('media_url')->nullable();
                $table->string('media_type', 30)->default('text');
                $table->string('filename', 150)->nullable();
                $table->string('message_id', 150)->nullable();
                $table->string('status', 30)->default('pending'); // pending, sent, delivered, failed
                $table->text('error_message')->nullable();
                $table->string('send_mode', 50)->default('web_admin'); // web_admin, api, android_gateway
                $table->timestamps();

                $table->index('session_id');
                $table->index('status');
                $table->index('receiver');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('device_messages');
    }
};
