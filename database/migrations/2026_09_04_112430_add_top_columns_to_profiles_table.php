<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->json('top_artists')->nullable();
            $table->json('top_songs')->nullable();
            $table->json('top_albums')->nullable();
            $table->json('top_friends')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['top_artists', 'top_songs', 'top_albums', 'top_friends']);
        });
    }
};