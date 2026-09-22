<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An admin's "Delete User" button should not be a real, permanent
     * DB::delete() — a moderation mistake or a bad-faith mass-report
     * should be recoverable. SoftDeletes gives a `deleted_at` column;
     * User::onlyTrashed() lists deleted accounts, User::withTrashed()
     * finds one, and $user->restore() undoes it. The account's own
     * self-service deletion (SettingsController::deleteAccount) can
     * keep behaving as a real hard delete if you want that distinction,
     * or switch to soft too — your call, but they don't have to match.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};