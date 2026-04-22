<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add purchase counter for branch-specific invoice numbering
     * ✅ Same pattern as invoice_counter for Sales
     * Format: P-INV-0001, P-INV-0002, etc. per branch
     */
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->integer('purchase_counter')->default(0)->after('invoice_counter');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('purchase_counter');
        });
    }
};
