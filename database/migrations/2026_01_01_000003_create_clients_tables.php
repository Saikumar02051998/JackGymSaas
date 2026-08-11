<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('member_id')->unique();
            $table->date('joining_date')->nullable();
            $table->string('lead_source', 50)->nullable();
            $table->foreignId('assigned_trainer_id')->nullable()->constrained('staff_profiles')->nullOnDelete();
            $table->string('gender', 20)->nullable();
            $table->date('dob')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->string('referral_code', 20)->nullable()->unique();
            $table->foreignId('referred_by')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['gym_id', 'status']);
            $table->index(['gym_id', 'joining_date']);
        });

        Schema::create('client_health_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->decimal('height', 6, 2)->nullable();
            $table->decimal('weight', 6, 2)->nullable();
            $table->decimal('bmi', 6, 2)->nullable();
            $table->decimal('body_fat', 6, 2)->nullable();
            $table->decimal('goal_weight', 6, 2)->nullable();
            $table->string('fitness_goal', 50)->nullable();
            $table->string('activity_level', 30)->nullable();
            $table->text('medical_notes')->nullable();
            $table->text('injuries')->nullable();
            $table->text('limitations')->nullable();
            $table->text('allergies')->nullable();
            $table->text('important_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_health_profiles');
        Schema::dropIfExists('clients');
    }
};
