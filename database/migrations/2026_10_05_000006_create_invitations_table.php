<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->string('label')->nullable(); // e.g. "The Mwangi Family"
            $table->unsignedSmallInteger('entitlement_count')->default(1);
            $table->string('status')->default('issued'); // issued | revoked | replaced
            $table->string('token_hash', 64)->unique();     // sha256 of raw token
            $table->text('token_encrypted')->nullable();     // encrypted copy so organizers can re-share
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('replaced_by_id')->nullable()->constrained('invitations')->nullOnDelete();
            $table->string('rsvp_status')->default('pending'); // pending | confirmed | declined | expired
            $table->unsignedSmallInteger('rsvp_guest_count')->nullable();
            $table->timestamp('rsvp_responded_at')->nullable();
            $table->string('rsvp_note')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'status']);
            $table->index(['event_id', 'rsvp_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
