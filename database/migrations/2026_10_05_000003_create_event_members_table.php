<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('committee'); // owner | committee | attendant
            $table->string('status')->default('active');  // invited | active | revoked
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
            $table->index('user_id');
        });

        Schema::create('event_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_member_id')->constrained('event_members')->cascadeOnDelete();
            $table->string('permission');
            $table->timestamps();

            $table->unique(['event_member_id', 'permission']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_permissions');
        Schema::dropIfExists('event_members');
    }
};
