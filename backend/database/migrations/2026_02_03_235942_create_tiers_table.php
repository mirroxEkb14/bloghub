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
        Schema::create('tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_profile_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('level');
            $table->string('tier_name', 50);
            $table->string('tier_desc', 255);
            $table->unsignedInteger('price');
            $table->char('currency', 3);

            $table->timestamps();

            $table->unique(['creator_profile_id', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiers');
    }
};
