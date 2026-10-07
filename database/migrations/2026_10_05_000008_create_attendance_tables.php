<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Successful check-ins only. The unique constraint is the server-side
        // authority that prevents duplicate check-ins even under concurrency.
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->foreignId('attendant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('guest_count')->default(1);
            $table->string('method')->default('qr'); // qr | manual
            $table->timestamp('checked_in_at');
            $table->timestamps();

            $table->unique(['event_id', 'invitation_id']);
            $table->index('attendant_id');
        });

        // Full audit trail of every verification attempt (allowed or denied).
        Schema::create('scan_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('invitation_id')->nullable()->constrained('invitations')->nullOnDelete();
            $table->foreignId('attendant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('outcome'); // valid | invalid | revoked | wrong_event | duplicate
            $table->string('token_hint', 12)->nullable(); // last characters only, never the full token
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['event_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_attempts');
        Schema::dropIfExists('attendance_records');
    }
};
