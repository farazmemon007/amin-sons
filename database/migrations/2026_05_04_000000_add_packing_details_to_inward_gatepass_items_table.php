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
        Schema::table('inward_gatepass_items', function (Blueprint $table) {
            $table->string('packing_type')->nullable();
            $table->decimal('packing_qty', 10, 2)->nullable();
            $table->decimal('piece_per_pack', 10, 2)->nullable();
            $table->decimal('loose_piece', 10, 2)->nullable();
            $table->string('unit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inward_gatepass_items', function (Blueprint $table) {
            $table->dropColumn(['packing_type', 'packing_qty', 'piece_per_pack', 'loose_piece', 'unit']);
        });
    }
};
