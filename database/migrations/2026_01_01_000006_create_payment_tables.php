<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
            $table->string('payment_no')->unique();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('membership_id')->nullable()->constrained('memberships')->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('membership_plans')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2);
            $table->string('payment_method', 30)->default('cash');
            $table->string('transaction_id', 100)->nullable();
            $table->string('gateway', 30)->nullable();
            $table->string('gateway_reference', 100)->nullable();
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'refunded', 'partially_refunded'])->default('pending');
            $table->date('payment_date');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['gym_id', 'payment_date']);
            $table->index(['client_id', 'status']);
            $table->index(['status', 'gateway']);
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->enum('transaction_type', ['create', 'authorize', 'capture', 'refund', 'partial_refund', 'verify', 'fail'])->default('create');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status', 30);
            $table->string('gateway', 30)->nullable();
            $table->string('gateway_reference', 100)->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_gateway_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('gateway', 30);
            $table->string('event', 100);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['gateway', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_logs');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payments');
    }
};
