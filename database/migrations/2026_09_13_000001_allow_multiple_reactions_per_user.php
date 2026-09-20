<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Previously one reaction per user per message (unique on
     * message_id+user_id) — picking a second type just switched the
     * existing one. Now a user can react with several different types
     * on the same message at once, like Discord, so uniqueness moves
     * to message_id+user_id+type instead.
     *
     * MySQL note: a unique index that covers a FK column is used by
     * InnoDB to support that foreign key, so dropping the unique
     * directly errors with "Cannot drop index ... needed in a foreign
     * key constraint". We drop the FK, drop the unique, add the new
     * unique, then re-add the FK pointing at the correct parent table.
     */
    public function up(): void
    {
        $this->rebuildUnique(
            'message_reactions',
            'message_reactions_message_id_foreign',
            'messages'
        );

        $this->rebuildUnique(
            'space_message_reactions',
            'space_message_reactions_message_id_foreign',
            'space_messages'
        );
    }

    public function down(): void
    {
        $this->rebuildUniqueReverse(
            'message_reactions',
            'message_reactions_message_id_foreign',
            'messages'
        );

        $this->rebuildUniqueReverse(
            'space_message_reactions',
            'space_message_reactions_message_id_foreign',
            'space_messages'
        );
    }

    protected function rebuildUnique(string $table, string $fkName, string $parentTable): void
    {
        // 1. Drop the FK on message_id (it's what's blocking the index drop)
        Schema::table($table, function (Blueprint $blueprint) use ($fkName) {
            $blueprint->dropForeign($fkName);
        });

        // 2. Drop the old 2-column unique index
        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropUnique(['message_id', 'user_id']);
        });

        // 3. Add the new 3-column unique index
        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->unique(['message_id', 'user_id', 'type']);
        });

        // 4. Re-add the FK pointing at the correct parent table
        Schema::table($table, function (Blueprint $blueprint) use ($parentTable) {
            $blueprint->foreign('message_id')
                      ->references('id')
                      ->on($parentTable)
                      ->onDelete('cascade');
        });
    }

    protected function rebuildUniqueReverse(string $table, string $fkName, string $parentTable): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($fkName) {
            $blueprint->dropForeign($fkName);
        });

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropUnique(['message_id', 'user_id', 'type']);
        });

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->unique(['message_id', 'user_id']);
        });

        Schema::table($table, function (Blueprint $blueprint) use ($parentTable) {
            $blueprint->foreign('message_id')
                      ->references('id')
                      ->on($parentTable)
                      ->onDelete('cascade');
        });
    }
};