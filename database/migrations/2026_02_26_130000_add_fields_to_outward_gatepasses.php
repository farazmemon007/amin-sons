<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('outward_gatepasses', function (Blueprint $table) {
            $table->string('driver_name')->nullable()->after('dc_no');
            $table->string('vehicle_number')->nullable()->after('driver_name');
            $table->json('items')->nullable()->after('vehicle_number');
            $table->string('issued_by')->nullable()->after('items');
        });
    }

    public function down()
    {
        Schema::table('outward_gatepasses', function (Blueprint $table) {
            $table->dropColumn(['driver_name', 'vehicle_number', 'items', 'issued_by']);
        });
    }
};
