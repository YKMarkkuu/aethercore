<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('conversation_participants', function (Blueprint $table) {
        $table->id();
        $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->timestamp('last_read_at')->nullable();
        $table->string('role')->default('member'); // admin, member
        $table->timestamps();
        
        $table->unique(['conversation_id', 'user_id']);
    });
}
};
