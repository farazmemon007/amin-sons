<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            // Add new columns to support approval workflow
            if (!Schema::hasColumn('stock_transfers', 'status')) {
                $table->enum('status', ['pending', 'approved', 'completed', 'cancelled'])->default('completed')->after('quantity');
            }
            if (!Schema::hasColumn('stock_transfers', 'stock_request_id')) {
                $table->unsignedBigInteger('stock_request_id')->nullable()->after('status');
            }
            if (!Schema::hasColumn('stock_transfers', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('stock_request_id');
            }
            if (!Schema::hasColumn('stock_transfers', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
        
        // Add foreign keys separately
        Schema::table('stock_transfers', function (Blueprint $table) {
            if (Schema::hasColumn('stock_transfers', 'stock_request_id')) {
                try {
                    $table->foreign('stock_request_id')->references('id')->on('stock_requests')->onDelete('set null');
                } catch (\Exception $e) {
                    // Foreign key might already exist
                }
            }
            if (Schema::hasColumn('stock_transfers', 'approved_by')) {
                try {
                    $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
                } catch (\Exception $e) {
                    // Foreign key might already exist
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropForeign(['stock_request_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['status', 'stock_request_id', 'approved_by', 'approved_at']);
        });
    }
};
