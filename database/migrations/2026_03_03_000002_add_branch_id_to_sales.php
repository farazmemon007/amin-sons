<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | SALES TABLE
        |--------------------------------------------------------------------------
        */
        Schema::table('sales', function (Blueprint $table) {

            // Add branch_id
            $table->unsignedBigInteger('branch_id')->default(1)->after('id');

            // Drop old unique invoice_no
            try {
                $table->dropUnique(['invoice_no']);
            } catch (\Throwable $e) {}

            // Make invoice unique per branch
            $table->unique(['branch_id', 'invoice_no']);

            // Add foreign key
            try {
                $table->foreign('branch_id')
                      ->references('id')
                      ->on('branches')
                      ->onDelete('restrict');
            } catch (\Throwable $e) {}
        });


        /*
        |--------------------------------------------------------------------------
        | SALE_ITEMS TABLE
        |--------------------------------------------------------------------------
        */
        Schema::table('sale_items', function (Blueprint $table) {

            // Add branch_id column
            $table->unsignedBigInteger('branch_id')
                  ->default(1)
                  ->after('id');

            // Optional: index for fast searching
            $table->index('branch_id');

            // Add foreign key
            try {
                $table->foreign('branch_id')
                      ->references('id')
                      ->on('branches')
                      ->onDelete('restrict');
            } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | SALE_ITEMS TABLE ROLLBACK
        |--------------------------------------------------------------------------
        */
        Schema::table('sale_items', function (Blueprint $table) {

            try {
                $table->dropForeign(['branch_id']);
            } catch (\Throwable $e) {}

            try {
                $table->dropIndex(['branch_id']);
            } catch (\Throwable $e) {}

            $table->dropColumn('branch_id');
        });


        /*
        |--------------------------------------------------------------------------
        | SALES TABLE ROLLBACK
        |--------------------------------------------------------------------------
        */
        Schema::table('sales', function (Blueprint $table) {

            try {
                $table->dropForeign(['branch_id']);
            } catch (\Throwable $e) {}

            try {
                $table->dropUnique(['branch_id', 'invoice_no']);
            } catch (\Throwable $e) {}

            try {
                $table->unique('invoice_no');
            } catch (\Throwable $e) {}

            $table->dropColumn('branch_id');
        });
    }
};