<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('space_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('space_messages', 'edited_at')) {
                $table->timestamp('edited_at')->nullable();
            }
            if (!Schema::hasColumn('space_messages', 'is_deleted')) {
                $table->boolean('is_deleted')->default(false);
            }
            // Nullable self-reference for reply/quote threading. Deletes here
            // are normally soft (is_deleted tombstone), so this FK rarely
            // actually fires — nullOnDelete just guards the edge case.
            if (!Schema::hasColumn('space_messages', 'reply_to_id')) {
                $table->foreignId('reply_to_id')->nullable()->constrained('space_messages')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('space_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reply_to_id');
            $table->dropColumn(['edited_at', 'is_deleted']);
        });
    }
};