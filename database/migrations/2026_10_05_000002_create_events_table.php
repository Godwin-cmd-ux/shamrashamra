<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('event_categories')->nullOnDelete();
            $table->string('title');
            $table->string('status')->default('draft')->index(); // draft|published|completed|cancelled|archived
            $table->timestamp('starts_at')->nullable();
            $table->string('timezone', 64)->default('Africa/Dar_es_Salaam');
            $table->string('venue_name')->nullable();
            $table->string('venue_address')->nullable();
            $table->string('map_link')->nullable();
            $table->text('description')->nullable();
            $table->string('host_names')->nullable();
            $table->string('dress_code')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamp('rsvp_deadline')->nullable();
            $table->unsignedInteger('guest_capacity')->nullable();
            $table->json('settings')->nullable(); // guest_categories, require_expense_approval, etc.
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['organizer_id', 'status']);
            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
