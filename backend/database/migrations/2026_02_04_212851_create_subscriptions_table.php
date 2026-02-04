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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('tier_id')
                ->constrained('tiers')
                ->cascadeOnDelete();

            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('sub_status', ['Active','Canceled']);

            $table->timestamps();

            $table->index(['user_id', 'tier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
