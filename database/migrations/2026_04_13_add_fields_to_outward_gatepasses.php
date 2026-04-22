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
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('outward_gatepasses', 'vehicle_type')) {
                $table->string('vehicle_type')->nullable()->after('vehicle_number');
            }
            if (!Schema::hasColumn('outward_gatepasses', 'transport_rent')) {
                $table->decimal('transport_rent', 14, 2)->nullable()->after('billty_amount');
            }
            if (!Schema::hasColumn('outward_gatepasses', 'invoice_no')) {
                $table->string('invoice_no')->nullable()->after('dc_no');
            }
            if (!Schema::hasColumn('outward_gatepasses', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('invoice_no');
            }
            if (!Schema::hasColumn('outward_gatepasses', 'delivery_city')) {
                $table->string('delivery_city')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('outward_gatepasses', 'packing_notes')) {
                $table->text('packing_notes')->nullable()->after('items');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outward_gatepasses', function (Blueprint $table) {
            if (Schema::hasColumn('outward_gatepasses', 'vehicle_type')) {
                $table->dropColumn('vehicle_type');
            }
            if (Schema::hasColumn('outward_gatepasses', 'transport_rent')) {
                $table->dropColumn('transport_rent');
            }
            if (Schema::hasColumn('outward_gatepasses', 'invoice_no')) {
                $table->dropColumn('invoice_no');
            }
            if (Schema::hasColumn('outward_gatepasses', 'customer_name')) {
                $table->dropColumn('customer_name');
            }
            if (Schema::hasColumn('outward_gatepasses', 'delivery_city')) {
                $table->dropColumn('delivery_city');
            }
            if (Schema::hasColumn('outward_gatepasses', 'packing_notes')) {
                $table->dropColumn('packing_notes');
            }
        });
    }
};
