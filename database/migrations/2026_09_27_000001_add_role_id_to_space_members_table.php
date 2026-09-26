<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('space_members', function (Blueprint $table) {
            // Nullable + nullOnDelete: if a role is ever deleted, members
            // holding it fall back to "no role" (treated as the space's
            // default role by Space::getUserRole()) rather than the FK
            // blocking the role's deletion or cascading member removal.
            if (!Schema::hasColumn('space_members', 'role_id')) {
                $table->foreignId('role_id')->nullable()->after('role')->constrained('space_roles')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('space_members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });
    }
};
