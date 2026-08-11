<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->date('follow_up_date');
            $table->time('follow_up_time')->nullable();
            $table->string('type', 50);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled', 'rescheduled', 'overdue'])->default('pending');
            $table->string('outcome')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['gym_id', 'status']);
            $table->index(['gym_id', 'follow_up_date']);
            $table->index(['staff_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('followups');
    }
};
