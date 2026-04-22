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
        Schema::table('purchases', function (Blueprint $table) {
            // ✅ ERP STANDARD: Transport name field added for complete purchase documentation
            // Used for logistics tracking and delivery documentation
            if (!Schema::hasColumn('purchases', 'transport_name')) {
                $table->string('transport_name')->nullable()->after('note');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'transport_name')) {
                $table->dropColumn('transport_name');
            }
        });
    }
};
