<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Created now (alongside Block) even though the Report UI ships
     * later — schema is the expensive part to retrofit, the controller
     * and views are cheap to add on top once this exists.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();

            // Polymorphic target: a Post, Comment, Message, SpaceMessage,
            // or User. The controller that writes here must validate the
            // incoming type string against an allowlist — never trust a
            // raw class name supplied by the client.
            $table->morphs('reportable');

            $table->string('reason'); // spam, harassment, hate_speech, nudity, violence, other
            $table->text('details')->nullable();

            $table->string('status')->default('pending'); // pending, actioned, dismissed
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};