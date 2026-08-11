<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weight_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->decimal('weight', 6, 2);
            $table->decimal('height', 6, 2)->nullable();
            $table->decimal('bmi', 6, 2)->nullable();
            $table->decimal('body_fat', 6, 2)->nullable();
            $table->date('record_date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'record_date']);
        });

        Schema::create('body_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->decimal('chest', 6, 2)->nullable();
            $table->decimal('waist', 6, 2)->nullable();
            $table->decimal('hip', 6, 2)->nullable();
            $table->decimal('arms', 6, 2)->nullable();
            $table->decimal('thigh', 6, 2)->nullable();
            $table->json('other')->nullable();
            $table->date('record_date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'record_date']);
        });

        Schema::create('fitness_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('type', 50);
            $table->decimal('starting_value', 8, 2)->nullable();
            $table->decimal('current_value', 8, 2)->nullable();
            $table->decimal('target_value', 8, 2)->nullable();
            $table->decimal('progress_percent', 6, 2)->default(0);
            $table->date('target_date')->nullable();
            $table->enum('status', ['active', 'achieved', 'abandoned'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fitness_goals');
        Schema::dropIfExists('body_measurements');
        Schema::dropIfExists('weight_records');
    }
};
