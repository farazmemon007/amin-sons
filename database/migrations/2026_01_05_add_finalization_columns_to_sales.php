<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Add finalization columns if they don't exist
            if (!Schema::hasColumn('sales', 'is_posted')) {
                $table->boolean('is_posted')->default(0)->after('total_net');
            }
            if (!Schema::hasColumn('sales', 'posted_at')) {
                $table->timestamp('posted_at')->nullable()->after('is_posted');
            }
            if (!Schema::hasColumn('sales', 'finalized_at')) {
                $table->timestamp('finalized_at')->nullable()->after('posted_at');
            }
            if (!Schema::hasColumn('sales', 'finalized_by')) {
                $table->unsignedBigInteger('finalized_by')->nullable()->after('finalized_at');
            }
        });

        // Add ready_for_delivery column to sale_items if it doesn't exist
        Schema::table('sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_items', 'ready_for_delivery')) {
                $table->boolean('ready_for_delivery')->default(0)->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'is_posted',
                'posted_at',
                'finalized_at',
                'finalized_by'
            ]);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('ready_for_delivery');
        });
    }
};
