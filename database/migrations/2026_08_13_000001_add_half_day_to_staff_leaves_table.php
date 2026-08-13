<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_leaves', function (Blueprint $table) {
            $table->boolean('is_half_day')->default(false)->after('end_date');
            $table->decimal('days', 5, 2)->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('staff_leaves', function (Blueprint $table) {
            $table->dropColumn('is_half_day');
            $table->integer('days')->default(1)->change();
        });
    }
};
