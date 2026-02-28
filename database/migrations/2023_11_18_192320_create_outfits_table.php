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
        Schema::create('outfits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('file');
            $table->string('description')->nullable();
            $table->date('outfit_date');
            $table->unsignedInteger('season')->nullable();

            $table->unsignedBigInteger('tops')->nullable();
            $table->foreign('tops')->references('id')->on('items')->onDelete('cascade');

            $table->unsignedBigInteger('outer')->nullable();
            $table->foreign('outer')->references('id')->on('items')->onDelete('cascade');

            $table->unsignedBigInteger('bottoms')->nullable();
            $table->foreign('bottoms')->references('id')->on('items')->onDelete('cascade');

            $table->unsignedBigInteger('shoes')->nullable();
            $table->foreign('shoes')->references('id')->on('items')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outfits');
    }
};
