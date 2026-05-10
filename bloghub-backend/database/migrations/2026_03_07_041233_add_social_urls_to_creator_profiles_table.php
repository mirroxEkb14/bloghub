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
        Schema::table('creator_profiles', function (Blueprint $table) {
            $table->string('telegram_url', 255)->nullable()->after('profile_cover_path');
            $table->string('instagram_url', 255)->nullable()->after('telegram_url');
            $table->string('facebook_url', 255)->nullable()->after('instagram_url');
            $table->string('youtube_url', 255)->nullable()->after('facebook_url');
            $table->string('twitch_url', 255)->nullable()->after('youtube_url');
            $table->string('website_url', 255)->nullable()->after('twitch_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('creator_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_url',
                'instagram_url',
                'facebook_url',
                'youtube_url',
                'twitch_url',
                'website_url',
            ]);
        });
    }
};
