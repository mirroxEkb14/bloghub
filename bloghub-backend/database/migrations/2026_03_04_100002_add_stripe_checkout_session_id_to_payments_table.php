<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('stripe_checkout_session_id', 255)->nullable()->after('subscription_id');
            $table->unique('stripe_checkout_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['stripe_checkout_session_id']);
            $table->dropColumn('stripe_checkout_session_id');
        });
    }
};
