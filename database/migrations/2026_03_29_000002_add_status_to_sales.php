<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add status column to sales table
     * Status tracks the lifecycle of a sale:
     * - 'draft': Sale created but not yet posted
     * - 'posted': Sale posted (warehouse selected if needed)
     * - 'completed': Delivery challan generated
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'status')) {
                $table->string('status')
                    ->default('posted')
                    ->after('total_net')
                    ->index()
                    ->comment('Sale status: draft, posted, completed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
