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
        Schema::create('creator_profile_tag', function (Blueprint $table) {
            $table->foreignId('creator_profile_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('tag_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->primary(['creator_profile_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creator_profile_tag');
    }
};
