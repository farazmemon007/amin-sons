<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_warehouses', function (Blueprint $table) {
            // Add branch_id so warehouse assignments are branch-specific.
            // This fixes the "same warehouse in multiple branches shows selected everywhere" bug.
            $table->unsignedBigInteger('branch_id')->nullable()->after('user_id');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('user_warehouses', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
