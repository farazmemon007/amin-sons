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
        Schema::table('vendor_ledgers', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('vendor_id');
            
            // If you want to enforce foreign key:
            // $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_ledgers', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
    }
};
