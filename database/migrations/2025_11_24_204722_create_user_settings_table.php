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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // メール通知
            $table->boolean('email_registration')->default(true);
            $table->boolean('email_password_reset')->default(true);
            $table->boolean('email_withdrawal')->default(true);

            // アプリ内通知（Reverb）
            $table->boolean('inapp_like')->default(true);
            $table->boolean('inapp_comment')->default(true);
            $table->boolean('inapp_follow')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
