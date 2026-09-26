<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'notify_email')) {
                $table->boolean('notify_email')->default(true);
            }
            if (!Schema::hasColumn('profiles', 'notify_friend_requests')) {
                $table->boolean('notify_friend_requests')->default(true);
            }
            if (!Schema::hasColumn('profiles', 'notify_messages')) {
                $table->boolean('notify_messages')->default(true);
            }
            if (!Schema::hasColumn('profiles', 'notify_likes_comments')) {
                $table->boolean('notify_likes_comments')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (Schema::hasColumn('profiles', 'notify_email')) {
                $table->dropColumn('notify_email');
            }
            if (Schema::hasColumn('profiles', 'notify_friend_requests')) {
                $table->dropColumn('notify_friend_requests');
            }
            if (Schema::hasColumn('profiles', 'notify_messages')) {
                $table->dropColumn('notify_messages');
            }
            if (Schema::hasColumn('profiles', 'notify_likes_comments')) {
                $table->dropColumn('notify_likes_comments');
            }
        });
    }
};
