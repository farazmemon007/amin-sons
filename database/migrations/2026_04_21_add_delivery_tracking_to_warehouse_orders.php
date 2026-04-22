<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add delivery tracking fields to warehouse_orders (DC table)
     * Tracks how much has been delivered via gatepasses vs remaining for future delivery
     * 
     * Scenario: DC has 5 pieces, but only 2 undamaged pieces shipped in first gatepass
     * - delivered_qty = 2 (actual delivered)
     * - remaining_qty = 3 (to be delivered later)
     * 
     * This allows manager to see which DCs need followup delivery
     */
    public function up(): void
    {
        Schema::table('warehouse_orders', function (Blueprint $table) {
            $table->decimal('delivered_qty', 10, 2)->nullable()->default(0)->after('items')->comment('Total quantity delivered via gatepasses');
            $table->decimal('remaining_qty', 10, 2)->nullable()->default(0)->after('delivered_qty')->comment('Quantity still pending delivery');
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_orders', function (Blueprint $table) {
            $table->dropColumn(['delivered_qty', 'remaining_qty']);
        });
    }
};
