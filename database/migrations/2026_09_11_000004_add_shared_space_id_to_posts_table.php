<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Nullable, nullOnDelete: if the Space is later deleted, the
            // post survives with a "no longer available" card.
            $table->foreignId('shared_space_id')->nullable()->constrained('spaces')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shared_space_id');
        });
    }
};