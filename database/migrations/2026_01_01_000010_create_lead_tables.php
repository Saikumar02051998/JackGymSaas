<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('source', 50)->nullable();
            $table->foreignId('interested_plan_id')->nullable()->constrained('membership_plans')->nullOnDelete();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->enum('status', ['new', 'contacted', 'interested', 'trial', 'converted', 'not_interested', 'lost'])->default('new');
            $table->date('follow_up_date')->nullable();
            $table->text('notes')->nullable();
            $table->date('converted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['gym_id', 'status']);
            $table->index(['gym_id', 'follow_up_date']);
        });

        Schema::create('lead_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->date('follow_up_date');
            $table->time('follow_up_time')->nullable();
            $table->string('type', 50)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->string('outcome')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('trials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->date('trial_start');
            $table->date('trial_end');
            $table->foreignId('assigned_trainer_id')->nullable()->constrained('staff_profiles')->nullOnDelete();
            $table->enum('status', ['active', 'completed', 'converted', 'expired', 'cancelled'])->default('active');
            $table->date('follow_up_date')->nullable();
            $table->date('converted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['gym_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trials');
        Schema::dropIfExists('lead_followups');
        Schema::dropIfExists('leads');
    }
};
