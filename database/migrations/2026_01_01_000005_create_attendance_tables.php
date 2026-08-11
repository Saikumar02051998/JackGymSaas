<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'left_early'])->default('present');
            $table->unsignedBigInteger('marked_by')->nullable();
            $table->enum('source', ['manual', 'reception', 'member_id', 'qr', 'biometric', 'api'])->default('reception');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'attendance_date']);
            $table->index(['gym_id', 'attendance_date']);
        });

        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff_profiles')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->integer('working_minutes')->nullable();
            $table->boolean('is_late')->default(false);
            $table->boolean('is_early_departure')->default(false);
            $table->integer('overtime_minutes')->default(0);
            $table->enum('status', ['present', 'absent', 'leave', 'half_day', 'holiday'])->default('present');
            $table->unsignedBigInteger('marked_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['staff_id', 'attendance_date']);
            $table->index(['gym_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendance');
        Schema::dropIfExists('attendance');
    }
};
