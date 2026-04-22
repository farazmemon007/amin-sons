<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Assign Piece unit (ID 1) to products without units
        DB::table('products')
            ->whereNull('unit_id')
            ->update(['unit_id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is not reversible without knowing which products originally had no unit
    }
};
