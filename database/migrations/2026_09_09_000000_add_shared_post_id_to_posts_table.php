<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Nullable self-reference: a repost is just a normal Post row
            // (own content = optional caption) pointing at the original.
            // nullOnDelete so a repost survives with a "no longer
            // available" placeholder if the original post is deleted,
            // rather than the repost itself vanishing or erroring.
            $table->foreignId('shared_post_id')->nullable()->constrained('posts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shared_post_id');
        });
    }
};