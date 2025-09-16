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
        Schema::create('keyword_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('keyword'); // "Tシャツ", "デニム", "革靴" など 
            $table->unsignedInteger('main_category')->nullable(); // Enum定義に紐付け 
            $table->unsignedInteger('sub_category')->nullable(); // 必要に応じて細分化 
            $table->unsignedInteger('color')->nullable(); // "黒T" → color=黒 に対応 
            $table->string('style')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keyword_mappings');
    }
};
