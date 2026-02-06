<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('creator_profiles', function (Blueprint $table) {
            $table->renameColumn('profile_avatar_url', 'profile_avatar_path');
            $table->renameColumn('profile_cover_url', 'profile_cover_path');
        });
    }

    public function down(): void
    {
        Schema::table('creator_profiles', function (Blueprint $table) {
            $table->renameColumn('profile_avatar_path', 'profile_avatar_url');
            $table->renameColumn('profile_cover_path', 'profile_cover_url');
        });
    }
};
