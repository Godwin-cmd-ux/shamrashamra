<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_templates', function (Blueprint $table) {
            $table->id();
            $table->string('scope')->default('platform'); // platform | event
            $table->foreignId('event_id')->nullable()->constrained('events')->cascadeOnDelete();
            $table->string('category')->nullable(); // wedding|sendoff|kitchen_party|...
            $table->string('name');
            $table->string('style'); // elegant|minimal|floral|traditional|modern|corporate|colorful
            $table->json('config')->nullable(); // palette, fonts, ornament, message defaults
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_templates');
    }
};
