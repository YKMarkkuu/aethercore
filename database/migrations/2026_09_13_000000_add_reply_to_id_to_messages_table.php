<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Nullable self-reference for reply/quote threading, mirroring
            // space_messages.reply_to_id — gives DMs reply parity with Spaces.
            $table->foreignId('reply_to_id')->nullable()->constrained('messages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reply_to_id');
        });
    }
};