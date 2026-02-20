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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('amount');
            $table->char('currency', 3);
            $table->dateTime('checkout_date');
            $table->string('card_last4', 4);
            $table->enum('payment_status', ['Pending','Completed','Failed']);

            $table->timestamps();

            $table->index(['subscription_id', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
