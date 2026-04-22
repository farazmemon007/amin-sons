<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add audit columns (created_by, updated_by) and remarks to customer_remaining
     * for tracking partial deliveries and user actions
     */
    public function up(): void
    {
        Schema::table('customer_remaining', function (Blueprint $table) {
            // Add remarks for tracking delivery details
            if (!Schema::hasColumn('customer_remaining', 'remarks')) {
                $table->text('remarks')->nullable()->after('notes');
            }

            // Add created_by to track which user created this record
            if (!Schema::hasColumn('customer_remaining', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('last_gatepass_id');
            }

            // Add updated_by to track last user who updated this record
            if (!Schema::hasColumn('customer_remaining', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_remaining', function (Blueprint $table) {
            if (Schema::hasColumn('customer_remaining', 'remarks')) {
                $table->dropColumn('remarks');
            }
            if (Schema::hasColumn('customer_remaining', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('customer_remaining', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
        });
    }
};
