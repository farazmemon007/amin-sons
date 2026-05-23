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
        // Sales table indexes
        Schema::table('sales', function (Blueprint $table) {
            $table->index('customer_id');
            $table->index('branch_id');
            $table->index('created_at');
        });

        // Purchases table indexes
        Schema::table('purchases', function (Blueprint $table) {
            $table->index('vendor_id');
            $table->index('branch_id');
            $table->index('created_at');
        });

        // Warehouse Stocks table indexes
        Schema::table('warehouse_stocks', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('branch_id');
            $table->index('warehouse_id');
        });

        // Receipts Vouchers table indexes
        Schema::table('receipts_vouchers', function (Blueprint $table) {
            $table->string('party_id', 191)->nullable()->change();
            $table->index('party_id');
            $table->index('receipt_date');
        });

        // Payment Vouchers table indexes
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->string('party_id', 191)->nullable()->change();
            $table->index('party_id');
            $table->index('receipt_date');
        });

        // Stocks table indexes
        Schema::table('stocks', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['branch_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['vendor_id']);
            $table->dropIndex(['branch_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('warehouse_stocks', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['branch_id']);
            $table->dropIndex(['warehouse_id']);
        });

        Schema::table('receipts_vouchers', function (Blueprint $table) {
            $table->dropIndex(['party_id']);
            $table->dropIndex(['receipt_date']);
            $table->text('party_id')->nullable()->change();
        });

        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->dropIndex(['party_id']);
            $table->dropIndex(['receipt_date']);
            $table->text('party_id')->nullable()->change();
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['branch_id']);
        });
    }
};
