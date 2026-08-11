<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->enum('appointment_type', ['pt', 'consultation', 'trial', 'followup', 'general'])->default('general');
            $table->date('appointment_date');
            $table->time('appointment_time')->nullable();
            $table->integer('duration_minutes')->default(30);
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['gym_id', 'appointment_date']);
            $table->index(['staff_id', 'status']);
        });

        Schema::create('personal_training_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('trainer_id')->constrained('staff_profiles')->cascadeOnDelete();
            $table->integer('session_no')->nullable();
            $table->integer('package_sessions')->default(0);
            $table->date('session_date');
            $table->time('session_time')->nullable();
            $table->integer('duration_minutes')->default(60);
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['gym_id', 'session_date']);
            $table->index(['trainer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_training_sessions');
        Schema::dropIfExists('appointments');
    }
};
