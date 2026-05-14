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
        Schema::table('inward_gatepasses', function (Blueprint $table) {
            $table->string('vehicle_type')->nullable()->after('transport_name');
            $table->string('vehicle_no')->nullable()->after('vehicle_type');
            $table->string('driver_name')->nullable()->after('vehicle_no');
            $table->string('driver_no')->nullable()->after('driver_name');
            $table->date('dispatch_date')->nullable()->after('driver_no');
            $table->string('delivery_challan_no')->nullable()->after('dispatch_date');
            $table->decimal('freight_charges', 12, 2)->nullable()->default(0)->after('delivery_challan_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inward_gatepasses', function (Blueprint $table) {
            $table->dropColumn([
                'vehicle_type',
                'vehicle_no',
                'driver_name',
                'driver_no',
                'dispatch_date',
                'delivery_challan_no',
                'freight_charges'
            ]);
        });
    }
};
