<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('space_roles', function (Blueprint $table) {
            if (!Schema::hasColumn('space_roles', 'is_owner')) {
                $table->boolean('is_owner')->default(false)->after('is_default');
            }
        });

        // Backfill: mark the existing Owner role on every space
        \DB::table('space_roles')->where('name', 'Owner')->update(['is_owner' => true]);
    }

    public function down(): void
    {
        Schema::table('space_roles', function (Blueprint $table) {
            $table->dropColumn('is_owner');
        });
    }
};