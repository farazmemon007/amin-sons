<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ERP: Role-Based Warehouse Access & Responsibility Assignment
     * 
     * This pivot table controls WHICH users are responsible for WHICH warehouses.
     * Rules:
     *  - Super Admin        → access to all warehouses (no record needed)
     *  - Branch Admin       → access to all warehouses in their own branch (no record needed)
     *  - Sales/Purchase/Others → ONLY warehouses listed in this table for their user_id
     *  - Cross-branch: a single user (e.g. incharge) can be assigned to warehouses
     *    across multiple branches by the Super Admin.
     */
    public function up(): void
    {
        Schema::create('user_warehouses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->boolean('is_incharge')->default(false); // main responsible person
            $table->text('notes')->nullable();              // optional notes about responsibility
            $table->timestamps();

            $table->unique(['user_id', 'warehouse_id']); // prevent duplicates
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_warehouses');
    }
};
