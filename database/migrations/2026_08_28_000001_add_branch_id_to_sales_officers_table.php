<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('sales_officers', 'branch_id')) {
            Schema::table('sales_officers', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->default(1)->after('id');
                $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            });

            // Set existing records to branch 1 if any branch exists
            $firstBranchId = DB::table('branches')->value('id') ?? 1;
            DB::table('sales_officers')->whereNull('branch_id')->update(['branch_id' => $firstBranchId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sales_officers', 'branch_id')) {
            Schema::table('sales_officers', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }
    }
};
