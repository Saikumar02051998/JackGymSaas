<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gyms', function (Blueprint $table) {
            $table->foreignId('subscription_plan_id')->nullable()->after('status')->constrained('subscription_plans')->nullOnDelete();
            $table->string('subscription_billing_cycle', 20)->nullable()->after('subscription_plan_id');
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_billing_cycle');
            $table->string('subscription_status', 20)->default('trial')->after('subscription_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('gyms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subscription_plan_id');
            $table->dropColumn(['subscription_billing_cycle', 'subscription_expires_at', 'subscription_status']);
        });
    }
};
