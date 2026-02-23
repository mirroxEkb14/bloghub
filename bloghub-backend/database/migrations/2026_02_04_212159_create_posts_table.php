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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('creator_profile_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('required_tier_id')
                ->nullable()
                ->constrained('tiers')
                ->nullOnDelete();

            $table->string('slug', 255);
            $table->string('title', 50);
            $table->text('content_text');
            $table->string('media_url', 255)->nullable();
            $table->enum('media_type', ['Image', 'Audio', 'Video'])->nullable();

            $table->timestamps();

            $table->unique(['creator_profile_id', 'slug']);
            $table->index(['creator_profile_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
