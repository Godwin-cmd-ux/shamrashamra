<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('other'); // caterer|decorator|photographer|venue|entertainment|transport|printer|other
            $table->string('contact_name')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('email')->nullable();
            $table->text('services')->nullable();
            $table->unsignedBigInteger('agreed_amount_minor')->default(0);
            $table->unsignedBigInteger('deposit_amount_minor')->default(0);
            $table->string('contract_path')->nullable();
            $table->string('status')->default('active'); // active | completed | cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
