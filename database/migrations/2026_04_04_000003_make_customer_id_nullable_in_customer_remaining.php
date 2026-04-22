<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Makes customer_id nullable in customer_remaining table to support
     * walking customers (sub_customers) who don't have a customer record.
     * 
     * Context: Walking customers are stored in sales.sub_customer (string)
     * with sales.customer_id = NULL. This migration allows tracking their
     * remaining/partially delivered items.
     */
    public function up(): void
    {
        Schema::table('customer_remaining', function (Blueprint $table) {
            // Make customer_id nullable to support walking customers
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            
            // Add optional column to store sub_customer name for easy reference
            if (!Schema::hasColumn('customer_remaining', 'sub_customer_name')) {
                $table->string('sub_customer_name')->nullable()->after('customer_id')->comment('For walking customers without a customer record');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_remaining', function (Blueprint $table) {
            // Revert customer_id back to NOT NULL
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
            
            // Drop the sub_customer_name column if it exists
            if (Schema::hasColumn('customer_remaining', 'sub_customer_name')) {
                $table->dropColumn('sub_customer_name');
            }
        });
    }
};
