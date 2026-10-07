<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedBigInteger('planned_amount_minor')->default(0);
            $table->string('currency', 3)->default('TZS');
            $table->timestamps();

            $table->unique(['event_id', 'name']);
        });

        Schema::create('pledges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('contributor_name');
            $table->string('contributor_contact')->nullable();
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3)->default('TZS');
            $table->date('due_date')->nullable();
            $table->string('status')->default('open'); // open | partially_paid | settled | cancelled
            $table->string('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['event_id', 'status']);
        });

        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('pledge_id')->nullable()->constrained('pledges')->nullOnDelete();
            $table->bigInteger('amount_minor'); // signed: reversals are negative
            $table->string('currency', 3)->default('TZS');
            $table->date('received_at');
            $table->string('method')->default('cash'); // cash | momo | bank | other
            $table->string('reference')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reverses_id')->nullable()->constrained('contributions')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reversal_reason')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'received_at']);
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('budget_category_id')->nullable()->constrained('budget_categories')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('description');
            $table->bigInteger('amount_minor'); // signed: reversals are negative
            $table->string('currency', 3)->default('TZS');
            $table->date('incurred_at');
            $table->string('payment_status')->default('pending'); // pending | paid
            $table->string('approval_status')->default('approved'); // pending | approved | not_required
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('receipt_path')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reverses_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reversal_reason')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'incurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('contributions');
        Schema::dropIfExists('pledges');
        Schema::dropIfExists('budget_categories');
    }
};
