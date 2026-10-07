<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Provider-independent notification outbox + per-attempt history.
        Schema::create('notification_outbox', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('invitation_id')->nullable()->constrained('invitations')->nullOnDelete();
            $table->string('channel'); // email | sms | whatsapp | inapp
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->string('status')->default('pending'); // pending | queued | sent | failed | manual
            $table->string('provider')->nullable();
            $table->string('provider_reference')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['event_id', 'channel', 'status']);
        });

        Schema::create('notification_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('notification_outbox')->cascadeOnDelete();
            $table->unsignedInteger('attempt')->default(1);
            $table->string('status'); // queued | sent | failed | manual
            $table->text('detail')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('notification_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_attempts');
        Schema::dropIfExists('notification_outbox');
    }
};
