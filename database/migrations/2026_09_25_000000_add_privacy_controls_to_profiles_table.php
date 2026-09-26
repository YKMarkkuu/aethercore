<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `is_public` already existed but was never actually enforced anywhere
     * (dead column). This replaces it with a proper 3-state `visibility`
     * column and adds the two other privacy controls from updates.txt
     * Tier 2 (#8): who can DM you, who can see your activity status.
     */
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'visibility')) {
                $table->string('visibility')->default('public')->after('is_public'); // public | friends | private
            }
            if (!Schema::hasColumn('profiles', 'dm_permission')) {
                $table->string('dm_permission')->default('everyone')->after('visibility'); // everyone | friends | nobody
            }
            if (!Schema::hasColumn('profiles', 'show_status_to')) {
                $table->string('show_status_to')->default('everyone')->after('dm_permission'); // everyone | friends | nobody
            }
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['visibility', 'dm_permission', 'show_status_to']);
        });
    }
};
