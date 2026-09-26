<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // A string column ('user' | 'moderator' | 'admin'), not a
            // boolean is_admin — this lets a moderator tier be added
            // later with no further migration. Every authorization
            // check should read $user->isAdmin() / $user->role; NEVER
            // hardcode a username/account check (e.g. checking for
            // "Markkuu" by name) anywhere in controllers or middleware.
            // The accounts are special because their `role` column says
            // so, not because the code recognizes their name.
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('status');
            }

            if (!Schema::hasColumn('users', 'suspended_until')) {
                $table->timestamp('suspended_until')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'banned_at')) {
                $table->timestamp('banned_at')->nullable()->after('suspended_until');
            }
            if (!Schema::hasColumn('users', 'moderation_note')) {
                $table->text('moderation_note')->nullable()->after('banned_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'suspended_until', 'banned_at', 'moderation_note']);
        });
    }
};