<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->decimal('purchase_cost', 12, 2)->nullable()->change();
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->decimal('purchase_price', 12, 2)->nullable()->change();
            $table->decimal('selling_price', 12, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->decimal('purchase_cost', 12, 2)->default(0)->change();
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->decimal('purchase_price', 12, 2)->default(0)->change();
            $table->decimal('selling_price', 12, 2)->default(0)->change();
        });
    }
};
