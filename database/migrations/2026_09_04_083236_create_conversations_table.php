<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('conversations', function (Blueprint $table) {
        $table->id();
        $table->string('type')->default('direct');
        $table->string('name')->nullable();
        $table->foreignId('space_id')->nullable();
        $table->timestamp('last_message_at')->nullable();
        $table->timestamps();
    });
}
};
