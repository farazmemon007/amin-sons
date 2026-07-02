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
        Schema::table('complaint_replacements', function (Blueprint $table) {
            $table->boolean('is_issued_part')->default(false)->after('quantity');
            $table->string('issued_part_name')->nullable()->after('is_issued_part');
            $table->boolean('is_collected_part')->default(false)->after('damaged_qty');
            $table->string('collected_part_name')->nullable()->after('is_collected_part');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaint_replacements', function (Blueprint $table) {
            $table->dropColumn(['is_issued_part', 'issued_part_name', 'is_collected_part', 'collected_part_name']);
        });
    }
};
