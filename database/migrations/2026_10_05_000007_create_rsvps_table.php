<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('status'); // confirmed | declined
            $table->unsignedSmallInteger('guest_count')->nullable();
            $table->string('note')->nullable();
            $table->string('source')->default('link'); // link | manual
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('responded_at');
            $table->timestamps();

            $table->index(['invitation_id', 'responded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsvps');
    }
};
