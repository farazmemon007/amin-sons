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
        Schema::table('outward_gatepasses', function (Blueprint $table) {
            // ✅ Add transport receipt image column for handwritten receipt
            if (!Schema::hasColumn('outward_gatepasses', 'transport_receipt_path')) {
                $table->string('transport_receipt_path')->nullable()->after('packing_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outward_gatepasses', function (Blueprint $table) {
            if (Schema::hasColumn('outward_gatepasses', 'transport_receipt_path')) {
                $table->dropColumn('transport_receipt_path');
            }
        });
    }
};
