<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
            $table->string('name');
            $table->integer('duration_days');
            $table->string('duration_label', 50);
            $table->decimal('price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2);
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['gym_id', 'status']);
        });

        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('membership_plans')->restrictOnDelete();
            $table->string('membership_no')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['upcoming', 'active', 'expired', 'cancelled', 'suspended', 'frozen'])->default('upcoming');
            $table->decimal('amount', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2);
            $table->string('payment_status', 20)->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['gym_id', 'status']);
            $table->index(['gym_id', 'end_date']);
            $table->index(['client_id', 'status']);
        });

        Schema::create('membership_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->constrained('memberships')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('membership_plans')->nullOnDelete();
            $table->enum('action', ['created', 'renewed', 'upgraded', 'downgraded', 'extended', 'frozen', 'resumed', 'cancelled', 'expired', 'suspended', 'activated']);
            $table->date('previous_end_date')->nullable();
            $table->date('new_end_date')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_histories');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('membership_plans');
    }
};
