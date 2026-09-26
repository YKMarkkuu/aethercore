<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'top_artists')) {
                $table->json('top_artists')->nullable();
            }
            if (!Schema::hasColumn('profiles', 'top_songs')) {
                $table->json('top_songs')->nullable();
            }
            if (!Schema::hasColumn('profiles', 'top_albums')) {
                $table->json('top_albums')->nullable();
            }
            if (!Schema::hasColumn('profiles', 'top_friends')) {
                $table->json('top_friends')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['top_artists', 'top_songs', 'top_albums', 'top_friends']);
        });
    }
};