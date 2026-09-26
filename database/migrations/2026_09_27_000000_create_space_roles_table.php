<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('space_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 7)->default('#99aab5'); // hex, e.g. #3a7bd5
            // Higher position = higher in the hierarchy. Used to decide
            // who can manage/assign whom (a role can never act on a role
            // with an equal or higher position than its own).
            $table->integer('position')->default(0);
            // Array of SpacePermission enum values, e.g. ["manage_channels", "kick_members"].
            $table->json('permissions')->nullable();
            // The role auto-assigned to new members who join with no
            // explicit role (there's always exactly one per space).
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['space_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('space_roles');
    }
};
