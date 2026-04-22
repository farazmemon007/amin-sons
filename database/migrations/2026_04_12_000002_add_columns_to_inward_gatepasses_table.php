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
        Schema::table('inward_gatepasses', function (Blueprint $table) {
            // Add missing columns for partial delivery support
            $table->text('note')->nullable()->after('remarks');
            $table->string('transport_name')->nullable()->after('note');
            $table->string('bilty_no')->nullable()->after('transport_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inward_gatepasses', function (Blueprint $table) {
            $table->dropColumn(['note', 'transport_name', 'bilty_no']);
        });
    }
};
