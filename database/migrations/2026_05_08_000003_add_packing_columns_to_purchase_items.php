<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_items', 'packing_type')) {
                $table->string('packing_type')->nullable()->after('qty');
            }
            if (!Schema::hasColumn('purchase_items', 'packing_qty')) {
                $table->decimal('packing_qty', 15, 2)->nullable()->after('packing_type');
            }
            if (!Schema::hasColumn('purchase_items', 'item_per_piece')) {
                $table->decimal('item_per_piece', 15, 2)->nullable()->after('packing_qty');
            }
            if (!Schema::hasColumn('purchase_items', 'loose_piece')) {
                $table->decimal('loose_piece', 15, 2)->nullable()->after('item_per_piece');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('purchase_items', 'packing_type')) {
                $columns[] = 'packing_type';
            }
            if (Schema::hasColumn('purchase_items', 'packing_qty')) {
                $columns[] = 'packing_qty';
            }
            if (Schema::hasColumn('purchase_items', 'item_per_piece')) {
                $columns[] = 'item_per_piece';
            }
            if (Schema::hasColumn('purchase_items', 'loose_piece')) {
                $columns[] = 'loose_piece';
            }
            
            if (count($columns) > 0) {
                $table->dropColumn($columns);
            }
        });
    }
};
